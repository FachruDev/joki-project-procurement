<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_admin_dashboard_separates_operational_dashboard_from_report_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Dashboard Summary')
            ->assertSee('Operational Feed')
            ->assertSee('Open Full Reports')
            ->assertDontSee('Export Dashboard Excel')
            ->assertDontSee('Vendor Report')
            ->assertDontSee('Purchase Report (PO)');
    }

    public function test_sidebar_hides_rfq_menu_for_users_without_rfq_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()->assertDontSee('RFQ List');
    }

    public function test_sidebar_shows_rfq_menu_for_users_with_rfq_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $response = $this->actingAs($procurement)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('RFQ List')
            ->assertSee('My RFQ')
            ->assertSee('My PO')
            ->assertSee('href="'.route('rfqs.my').'"', false)
            ->assertSee('href="'.route('pos.my').'"', false)
            ->assertDontSee('href="'.route('rfqs.create').'"', false)
            ->assertDontSee('href="'.route('pos.create').'"', false);
    }

    public function test_vendor_cannot_see_vendor_summary_cards_without_permission(): void
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
            ->assertDontSee('Total Vendor')
            ->assertDontSee('Vendor Approved vs Pending')
            ->assertDontSee('Vendor Report')
            ->assertDontSee('Operational Feed')
            ->assertSee('Vendor Invoice Status')
            ->assertSee('Vendor Monthly Trend');
    }
}
