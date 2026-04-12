<?php

namespace Tests\Feature\Procurement;

use App\Models\User;
use App\Models\Vendor;
use App\Policies\VendorPolicy;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class VendorApprovalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_approve_vendor_profile(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $procurement = User::factory()->create();
        $procurement->assignRole('Procurement');

        $vendorUser = User::factory()->create();
        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Pending,
        ]);

        $this->assertFalse(Gate::forUser($procurement)->allows('approve', $vendor));
        $this->assertTrue(Gate::forUser($admin)->allows('approve', $vendor));

        $policy = new VendorPolicy;

        $this->assertFalse($policy->approve($procurement, $vendor));
        $this->assertTrue($policy->approve($admin, $vendor));
    }
}
