<?php

namespace Tests\Feature\Procurement;

use App\Livewire\RFQ\Respond as RfqRespond;
use App\Models\Rfq;
use App\Models\RfqResponse;
use App\Models\User;
use App\Models\Vendor;
use App\RfqStatus;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RfqResponseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_submit_only_one_response_per_rfq(): void
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

        Livewire::actingAs($vendorUser)
            ->test(RfqRespond::class, ['rfq' => $rfq])
            ->set('price', '12500')
            ->set('notes', 'Best offer')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('rfq_responses', [
            'rfq_id' => $rfq->id,
            'vendor_id' => $vendor->id,
        ]);

        Livewire::actingAs($vendorUser)
            ->test(RfqRespond::class, ['rfq' => $rfq])
            ->set('price', '13000')
            ->call('submit')
            ->assertHasErrors('price');

        $this->assertSame(1, RfqResponse::query()->where('rfq_id', $rfq->id)->where('vendor_id', $vendor->id)->count());
    }

    public function test_vendor_must_be_assigned_to_respond(): void
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

        Livewire::actingAs($vendorUser)
            ->test(RfqRespond::class, ['rfq' => $rfq])
            ->assertForbidden();

        $this->assertDatabaseMissing('rfq_responses', [
            'rfq_id' => $rfq->id,
            'vendor_id' => $vendor->id,
        ]);
    }
}
