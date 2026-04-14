<?php

namespace Tests\Feature\Procurement;

use App\Models\User;
use App\Models\Vendor;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorSidebarVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_vendor_sidebar_hides_rfq_po_and_invoice_menus(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Pending,
        ]);

        $response = $this->actingAs($vendorUser)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertDontSee('RFQ List')
            ->assertDontSee('PO List')
            ->assertDontSee('My Invoice Upload')
            ->assertDontSee('Approved Invoices');
    }

    public function test_approved_vendor_sidebar_shows_rfq_po_and_invoice_menus(): void
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
            ->assertSee('RFQ List')
            ->assertSee('PO List')
            ->assertSee('My Invoice Upload')
            ->assertSee('Approved Invoices');
    }
}
