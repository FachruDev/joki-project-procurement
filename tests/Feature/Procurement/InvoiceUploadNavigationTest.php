<?php

namespace Tests\Feature\Procurement;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceUploadNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_sidebar_links_my_invoice_to_invoice_module(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        $response = $this->actingAs($vendorUser)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('My Invoice')
            ->assertSee('href="'.route('invoices.my').'"', false);
    }

    public function test_vendor_can_open_my_invoice_page_and_see_po_action_links(): void
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
        ]);

        $response = $this->actingAs($vendorUser)->get(route('invoices.my'));

        $response
            ->assertOk()
            ->assertSee('My Invoice')
            ->assertSee('#'.$purchaseOrder->id)
            ->assertSee(route('invoices.upload', $purchaseOrder), false);
    }
}
