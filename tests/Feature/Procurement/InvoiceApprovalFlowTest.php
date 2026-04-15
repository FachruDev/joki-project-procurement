<?php

namespace Tests\Feature\Procurement;

use App\InvoiceStatus;
use App\Livewire\Invoice\Approve as InvoiceApprove;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\PurchaseOrderStatus;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_cannot_be_approved_without_uploaded_file(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendor = Vendor::factory()->create([
            'status' => VendorStatus::Approved,
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'vendor_id' => $vendor->id,
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $invoice = Invoice::factory()->create([
            'po_id' => $purchaseOrder->id,
            'vendor_id' => $vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        Livewire::actingAs($admin)
            ->test(InvoiceApprove::class)
            ->call('approve', $invoice->id)
            ->assertHasErrors('selectedInvoiceIds');

        $invoice->refresh();

        $this->assertSame(InvoiceStatus::Pending, $invoice->status);
    }

    public function test_admin_can_approve_invoice_after_upload_exists(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendor = Vendor::factory()->create([
            'status' => VendorStatus::Approved,
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'vendor_id' => $vendor->id,
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $invoice = Invoice::factory()->create([
            'po_id' => $purchaseOrder->id,
            'vendor_id' => $vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $invoice->addMedia(UploadedFile::fake()->create('invoice.pdf', 150))
            ->toMediaCollection('invoice-files');

        Livewire::actingAs($admin)
            ->test(InvoiceApprove::class)
            ->call('approve', $invoice->id)
            ->assertHasNoErrors();

        $invoice->refresh();

        $this->assertSame(InvoiceStatus::Approved, $invoice->status);
    }

    public function test_admin_can_bulk_approve_pending_invoices(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendor = Vendor::factory()->create([
            'status' => VendorStatus::Approved,
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'vendor_id' => $vendor->id,
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $purchaseOrderTwo = PurchaseOrder::factory()->create([
            'vendor_id' => $vendor->id,
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $invoiceOne = Invoice::factory()->create([
            'po_id' => $purchaseOrder->id,
            'vendor_id' => $vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $invoiceTwo = Invoice::factory()->create([
            'po_id' => $purchaseOrderTwo->id,
            'vendor_id' => $vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $invoiceOne->addMedia(UploadedFile::fake()->create('invoice-one.pdf', 100))
            ->toMediaCollection('invoice-files');

        $invoiceTwo->addMedia(UploadedFile::fake()->create('invoice-two.pdf', 100))
            ->toMediaCollection('invoice-files');

        Livewire::actingAs($admin)
            ->test(InvoiceApprove::class)
            ->set('selectedInvoiceIds', [$invoiceOne->id, $invoiceTwo->id])
            ->call('bulkApprove')
            ->assertHasNoErrors();

        $invoiceOne->refresh();
        $invoiceTwo->refresh();

        $this->assertSame(InvoiceStatus::Approved, $invoiceOne->status);
        $this->assertSame(InvoiceStatus::Approved, $invoiceTwo->status);
    }
}
