<?php

namespace Tests\Feature\Procurement;

use App\Livewire\Report\Index as ReportIndex;
use App\Models\User;
use App\Models\Vendor;
use App\VendorStatus;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
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
            ->assertSee('System Comparison Report')
            ->assertSee('id="vendor-status-chart"', false)
            ->assertSee('id="operational-snapshot-chart"', false)
            ->assertSee('id="monthly-trend-chart"', false)
            ->assertSee("type: 'doughnut'", false)
            ->assertSee("type: 'bar'", false)
            ->assertSee("type: 'line'", false);

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

    public function test_report_page_can_export_excel_for_user_with_report_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        CarbonImmutable::setTestNow('2026-04-16 10:30:00');

        try {
            Excel::fake();

            $procurement = User::factory()->create();
            $procurement->assignRole('Procurement');

            Livewire::actingAs($procurement)
                ->test(ReportIndex::class)
                ->call('exportToExcel');

            Excel::assertDownloaded('system-report-20260416_103000.xlsx');
        } finally {
            CarbonImmutable::setTestNow();
        }
    }
}
