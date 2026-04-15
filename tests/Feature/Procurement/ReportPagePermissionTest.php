<?php

namespace Tests\Feature\Procurement;

use App\Models\User;
use App\Models\Vendor;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPagePermissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure report page and sidebar follow permission constraints.
     */
    public function test_report_page_requires_report_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        $this->actingAs($procurement)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('System Comparison Report');

        $this->actingAs($vendorUser)
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    public function test_reports_menu_is_visible_only_for_users_with_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        $this->actingAs($procurement)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Reports')
            ->assertSee('href="'.route('reports.index').'"', false);

        $this->actingAs($vendorUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('href="'.route('reports.index').'"', false)
            ->assertDontSee('Reports');
    }
}
