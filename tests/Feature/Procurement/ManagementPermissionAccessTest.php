<?php

namespace Tests\Feature\Procurement;

use App\Livewire\Admin\PermissionManagement;
use App\Livewire\Admin\UserManagement;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
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

    public function test_superadmin_user_assignments_are_protected_from_modification(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $superAdminUser = User::query()->where('email', 'superadmin@procurement.test')->firstOrFail();
        $vendorRoleId = Role::query()->where('name', 'Vendor')->value('id');

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->call('selectUser', $superAdminUser->id)
            ->set('selectedRoleIds', [$vendorRoleId])
            ->set('selectedPermissionIds', [])
            ->call('saveAssignments')
            ->assertHasErrors('selectedUserId');

        $this->assertTrue($superAdminUser->fresh()->hasRole('SuperAdmin'));
    }

    public function test_superadmin_role_permissions_are_protected_from_modification(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $superAdminRole = Role::query()->where('name', 'SuperAdmin')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(PermissionManagement::class)
            ->call('selectRole', $superAdminRole->id)
            ->set('rolePermissionIds', [])
            ->call('saveRolePermissions')
            ->assertHasErrors('selectedRoleId');

        $this->assertGreaterThan(0, $superAdminRole->fresh()->permissions()->count());
    }
}
