<?php

namespace Tests\Feature\Procurement;

use App\InvoiceStatus;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\PurchaseOrderStatus;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePrintAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_invoice_can_be_opened_on_print_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $invoice = Invoice::factory()->create([
            'po_id' => $purchaseOrder->id,
            'vendor_id' => $vendor->id,
            'status' => InvoiceStatus::Approved,
        ]);

        $response = $this->actingAs($vendorUser)->get(route('invoices.print', $invoice));

        $response
            ->assertOk()
            ->assertSee('Print / Save as PDF')
            ->assertSee('Invoice #'.$invoice->id);
    }

    public function test_pending_invoice_cannot_be_printed(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => PurchaseOrderStatus::Approved,
        ]);

        $invoice = Invoice::factory()->create([
            'po_id' => $purchaseOrder->id,
            'vendor_id' => $vendor->id,
            'status' => InvoiceStatus::Pending,
        ]);

        $response = $this->actingAs($vendorUser)->get(route('invoices.print', $invoice));

        $response->assertForbidden();
    }
}
