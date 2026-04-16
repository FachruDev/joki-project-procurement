<?php

namespace Tests\Feature\Procurement;

use App\Livewire\Admin\PermissionForm;
use App\Livewire\Admin\PermissionManagement;
use App\Livewire\Admin\RoleForm;
use App\Livewire\Admin\UserForm;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
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

    public function test_user_with_user_manage_permission_can_access_user_management_routes(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo('user.manage');

        $targetUser = User::factory()->create();

        $this->actingAs($user)
            ->get(route('management.users'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('management.users.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('management.users.edit', $targetUser))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('management.users.profile', $targetUser))
            ->assertOk();
    }

    public function test_user_with_permission_manage_permission_can_access_role_permission_routes(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo('permission.manage');

        $role = Role::query()->where('name', 'Vendor')->firstOrFail();
        $permission = Permission::query()->where('name', 'rfq.view')->firstOrFail();

        $this->actingAs($user)
            ->get(route('management.permissions'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('management.roles.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('management.roles.edit', $role))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('management.permissions.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('management.permissions.edit', $permission))
            ->assertOk();
    }

    public function test_user_form_can_create_user_with_role_assignment(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->givePermissionTo('user.manage');
        $vendorRoleId = Role::query()->where('name', 'Vendor')->value('id');

        Livewire::actingAs($admin)
            ->test(UserForm::class)
            ->set('name', 'Created User')
            ->set('email', 'created.user@example.test')
            ->set('password', 'password123')
            ->set('passwordConfirmation', 'password123')
            ->set('selectedRoleIds', [$vendorRoleId])
            ->call('save')
            ->assertHasNoErrors();

        $createdUser = User::query()->where('email', 'created.user@example.test')->first();

        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('Vendor'));
    }

    public function test_superadmin_user_profile_is_protected_from_modification(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->givePermissionTo('user.manage');

        $superAdminUser = User::query()->where('email', 'superadmin@procurement.test')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(UserForm::class, ['user' => $superAdminUser])
            ->set('name', 'Modified Super Admin')
            ->call('save')
            ->assertHasErrors('name');

        $freshSuperAdmin = $superAdminUser->fresh();

        $this->assertTrue($freshSuperAdmin->hasRole('SuperAdmin'));
        $this->assertSame('superadmin', $freshSuperAdmin->name);
    }

    public function test_superadmin_role_permissions_can_be_updated(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->givePermissionTo('permission.manage');

        $superAdminRole = Role::query()->where('name', 'SuperAdmin')->firstOrFail();
        $permissionId = Permission::query()->where('name', 'rfq.view')->value('id');

        Livewire::actingAs($admin)
            ->test(PermissionManagement::class)
            ->call('selectRole', $superAdminRole->id)
            ->set('rolePermissionIds', [$permissionId])
            ->call('saveRolePermissions')
            ->assertHasNoErrors();

        $this->assertTrue($superAdminRole->fresh()->hasPermissionTo('rfq.view'));
    }

    public function test_protected_role_names_cannot_be_renamed(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->givePermissionTo('permission.manage');

        $superAdminRole = Role::query()->where('name', 'SuperAdmin')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(RoleForm::class, ['role' => $superAdminRole])
            ->set('roleName', 'Chief Administrator')
            ->call('save')
            ->assertHasErrors('roleName');

        $this->assertSame('SuperAdmin', $superAdminRole->fresh()->name);
    }

    public function test_permission_form_can_create_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->givePermissionTo('permission.manage');

        Livewire::actingAs($admin)
            ->test(PermissionForm::class)
            ->set('permissionName', 'report.audit')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Permission::query()->where('name', 'report.audit')->exists());
    }
}
