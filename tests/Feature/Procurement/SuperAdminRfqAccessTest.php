<?php

namespace Tests\Feature\Procurement;

use App\Models\Rfq;
use App\Models\User;
use App\Models\Vendor;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminRfqAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_rfq_list_even_with_pending_vendor_profile(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $superAdmin = User::query()->where('email', 'superadmin@procurement.test')->firstOrFail();
        $procurement = User::query()->where('email', 'procurement@procurement.test')->firstOrFail();

        $rfq = Rfq::factory()->create([
            'title' => 'SuperAdmin Access RFQ',
            'created_by' => $procurement->id,
        ]);

        Vendor::factory()->create([
            'user_id' => $superAdmin->id,
            'status' => VendorStatus::Pending,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('rfqs.index'))
            ->assertOk()
            ->assertSee($rfq->title);
    }
}
