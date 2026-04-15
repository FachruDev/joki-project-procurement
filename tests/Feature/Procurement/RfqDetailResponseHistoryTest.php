<?php

namespace Tests\Feature\Procurement;

use App\Livewire\RFQ\Show as RfqShow;
use App\Models\Rfq;
use App\Models\RfqResponse;
use App\Models\User;
use App\Models\Vendor;
use App\RfqStatus;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class RfqDetailResponseHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify response history button and modal are rendered for RFQ detail.
     */
    public function test_rfq_detail_can_open_response_history_modal(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        $rfq = Rfq::factory()->create([
            'created_by' => $procurement->id,
            'status' => RfqStatus::Open,
        ]);

        $rfq->vendors()->attach($vendor->id);

        $response = RfqResponse::factory()->create([
            'rfq_id' => $rfq->id,
            'vendor_id' => $vendor->id,
            'price' => 15000,
            'notes' => 'Initial offer',
        ]);

        Activity::query()->create([
            'log_name' => config('activitylog.default_log_name'),
            'description' => 'rfq_response_updated',
            'subject_type' => RfqResponse::class,
            'subject_id' => $response->id,
            'causer_type' => User::class,
            'causer_id' => $vendorUser->id,
            'properties' => [
                'old' => ['price' => '15000.00', 'notes' => 'Initial offer'],
                'attributes' => ['price' => '14500.00', 'notes' => 'Updated offer'],
            ],
            'event' => 'updated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($procurement)
            ->get(route('rfqs.show', $rfq))
            ->assertOk()
            ->assertSee('View History');

        Livewire::actingAs($procurement)
            ->test(RfqShow::class, ['rfq' => $rfq])
            ->call('openResponseHistory', $response->id)
            ->assertSet('showResponseHistoryModal', true)
            ->assertSee('Response History')
            ->assertSee('Updated offer');
    }

    public function test_closed_rfq_cannot_be_deleted_and_delete_button_is_hidden(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $closedRfq = Rfq::factory()->create([
            'created_by' => $procurement->id,
            'status' => RfqStatus::Closed,
        ]);

        $this->actingAs($procurement)
            ->get(route('rfqs.show', $closedRfq))
            ->assertOk()
            ->assertDontSee('Delete RFQ?');

        Livewire::actingAs($procurement)
            ->test(RfqShow::class, ['rfq' => $closedRfq])
            ->call('deleteRfq')
            ->assertForbidden();
    }
}
