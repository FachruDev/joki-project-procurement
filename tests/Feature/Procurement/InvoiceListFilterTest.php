<?php

namespace Tests\Feature\Procurement;

use App\InvoiceStatus;
use App\Livewire\Invoice\ListAll;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\PurchaseOrderStatus;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceListFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_list_supports_status_vendor_and_date_filters(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendorOne = Vendor::factory()->create([
            'status' => VendorStatus::Approved,
        ]);

        $vendorTwo = Vendor::factory()->create([
            'status' => VendorStatus::Approved,
        ]);

        $purchaseOrderOne = PurchaseOrder::factory()->create([
            'vendor_id' => $vendorOne->id,
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $purchaseOrderTwo = PurchaseOrder::factory()->create([
            'vendor_id' => $vendorTwo->id,
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $purchaseOrderThree = PurchaseOrder::factory()->create([
            'vendor_id' => $vendorTwo->id,
            'created_by' => $procurement->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $pendingInvoice = Invoice::factory()->create([
            'po_id' => $purchaseOrderOne->id,
            'vendor_id' => $vendorOne->id,
            'status' => InvoiceStatus::Pending,
            'created_at' => now()->subDays(4),
        ]);

        $approvedInvoice = Invoice::factory()->create([
            'po_id' => $purchaseOrderTwo->id,
            'vendor_id' => $vendorTwo->id,
            'status' => InvoiceStatus::Approved,
            'created_at' => now()->subDay(),
        ]);

        $rejectedInvoice = Invoice::factory()->create([
            'po_id' => $purchaseOrderThree->id,
            'vendor_id' => $vendorTwo->id,
            'status' => InvoiceStatus::Rejected,
            'created_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(ListAll::class)
            ->assertSee('#'.$pendingInvoice->id)
            ->assertSee('#'.$approvedInvoice->id)
            ->assertSee('#'.$rejectedInvoice->id)
            ->set('statusFilter', 'approved')
            ->assertSee('#'.$approvedInvoice->id)
            ->assertDontSee('#'.$pendingInvoice->id)
            ->assertDontSee('#'.$rejectedInvoice->id)
            ->set('statusFilter', 'all')
            ->set('searchVendor', $vendorOne->company_name)
            ->assertSee('#'.$pendingInvoice->id)
            ->assertDontSee('#'.$approvedInvoice->id)
            ->assertDontSee('#'.$rejectedInvoice->id)
            ->set('searchVendor', '')
            ->set('dateFrom', now()->subDays(2)->toDateString())
            ->set('dateTo', now()->toDateString())
            ->assertDontSee('#'.$pendingInvoice->id)
            ->assertSee('#'.$approvedInvoice->id)
            ->assertSee('#'.$rejectedInvoice->id);
    }
}
