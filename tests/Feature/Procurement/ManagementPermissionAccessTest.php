<?php

namespace Tests\Feature\Procurement;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementPermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_management_permissions_cannot_access_management_pages(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Vendor');

        $this->actingAs($user)
            ->get(route('management.users'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('management.permissions'))
            ->assertForbidden();
    }

    public function test_user_with_user_manage_permission_can_access_user_management_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo('user.manage');

        $this->actingAs($user)
            ->get(route('management.users'))
            ->assertOk();
    }

    public function test_user_with_permission_manage_permission_can_access_role_permission_page(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo('permission.manage');

        $this->actingAs($user)
            ->get(route('management.permissions'))
            ->assertOk();
    }
}
