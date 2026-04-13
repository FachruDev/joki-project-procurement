<?php

namespace Tests\Feature\Procurement;

use App\Livewire\Invoice\Approve as InvoiceApprove;
use App\Livewire\Invoice\Upload as InvoiceUpload;
use App\Livewire\RFQ\Create as RfqCreate;
use App\Livewire\Vendor\Register as VendorRegister;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\InAppNotification;
use App\PurchaseOrderStatus;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_receives_notification_when_profile_is_approved(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Pending,
        ]);

        Livewire::actingAs($admin)
            ->test(VendorRegister::class)
            ->call('approve', $vendor->id)
            ->assertHasNoErrors();

        Notification::assertSentTo(
            $vendorUser,
            InAppNotification::class,
            fn (InAppNotification $notification): bool => $notification->title === 'Vendor Approved',
        );
    }

    public function test_only_assigned_vendors_receive_rfq_creation_notifications(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        Notification::fake();

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $assignedVendorUserOne = User::factory()->create();
        $assignedVendorUserOne->assignRole('Vendor');
        $assignedVendorOne = Vendor::factory()->create([
            'user_id' => $assignedVendorUserOne->id,
            'status' => VendorStatus::Approved,
        ]);

        $assignedVendorUserTwo = User::factory()->create();
        $assignedVendorUserTwo->assignRole('Vendor');
        $assignedVendorTwo = Vendor::factory()->create([
            'user_id' => $assignedVendorUserTwo->id,
            'status' => VendorStatus::Approved,
        ]);

        $unassignedVendorUser = User::factory()->create();
        $unassignedVendorUser->assignRole('Vendor');
        Vendor::factory()->create([
            'user_id' => $unassignedVendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        Livewire::actingAs($procurement)
            ->test(RfqCreate::class)
            ->set('title', 'Server Procurement Q2')
            ->set('description', 'Supply and support package for application servers.')
            ->set('deadline', now()->addDays(5)->format('Y-m-d H:i:s'))
            ->set('vendorIds', [$assignedVendorOne->id, $assignedVendorTwo->id])
            ->call('save')
            ->assertHasNoErrors();

        $rfq = Rfq::query()->first();
        $this->assertNotNull($rfq);

        Notification::assertSentTo($assignedVendorUserOne, InAppNotification::class);
        Notification::assertSentTo($assignedVendorUserTwo, InAppNotification::class);
        Notification::assertNotSentTo($unassignedVendorUser, InAppNotification::class);
    }

    public function test_invoice_upload_and_approval_send_notifications_to_expected_users(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        Notification::fake();
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'vendor_id' => $vendor->id,
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        Livewire::actingAs($vendorUser)
            ->test(InvoiceUpload::class, ['purchaseOrder' => $purchaseOrder])
            ->set('invoiceFile', UploadedFile::fake()->create('invoice.pdf', 120))
            ->call('save')
            ->assertHasNoErrors();

        Notification::assertSentTo(
            $admin,
            InAppNotification::class,
            fn (InAppNotification $notification): bool => $notification->title === 'Invoice Uploaded',
        );

        $invoice = Invoice::query()->where('po_id', $purchaseOrder->id)->first();
        $this->assertNotNull($invoice);

        Livewire::actingAs($admin)
            ->test(InvoiceApprove::class)
            ->call('approve', $invoice->id)
            ->assertHasNoErrors();

        Notification::assertSentTo(
            $vendorUser,
            InAppNotification::class,
            fn (InAppNotification $notification): bool => $notification->title === 'Invoice Approved',
        );
    }
}
