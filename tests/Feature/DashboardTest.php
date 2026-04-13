<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function test_admin_dashboard_displays_procurement_reports_sections(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Dashboard Summary')
            ->assertSee('Vendor Report')
            ->assertSee('RFQ Report')
            ->assertSee('Purchase Report (PO)')
            ->assertSee('Invoice Report');
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

        $response->assertOk()->assertSee('RFQ List');
    }
}
