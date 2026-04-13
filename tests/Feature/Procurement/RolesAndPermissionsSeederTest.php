<?php

namespace Tests\Feature\Procurement;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_permissions_are_seeded_correctly(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $permissions = [
            'vendor.manage',
            'vendor.approve',
            'rfq.create',
            'rfq.view',
            'rfq.respond',
            'po.create',
            'po.view',
            'gr.create',
            'invoice.upload',
            'invoice.approve',
            'user.manage',
            'permission.manage',
        ];

        foreach ($permissions as $permission) {
            $this->assertTrue(Permission::query()->where('name', $permission)->exists());
        }

        $admin = Role::findByName('Admin', 'web');
        $superAdmin = Role::findByName('SuperAdmin', 'web');
        $procurement = Role::findByName('Procurement', 'web');
        $vendor = Role::findByName('Vendor', 'web');

        $this->assertCount(count($permissions), $admin->permissions);
        $this->assertCount(count($permissions), $superAdmin->permissions);
        $this->assertTrue($admin->hasPermissionTo('invoice.approve'));
        $this->assertTrue($admin->hasPermissionTo('user.manage'));
        $this->assertTrue($superAdmin->hasPermissionTo('permission.manage'));
        $this->assertTrue($procurement->hasPermissionTo('po.create'));
        $this->assertFalse($procurement->hasPermissionTo('invoice.approve'));
        $this->assertTrue($vendor->hasPermissionTo('invoice.upload'));
        $this->assertFalse($vendor->hasPermissionTo('vendor.approve'));

        $superAdminUser = User::query()->where('email', 'superadmin@procurement.test')->first();

        $this->assertNotNull($superAdminUser);
        $this->assertSame('superadmin', $superAdminUser->name);
        $this->assertTrue($superAdminUser->hasRole('SuperAdmin'));
    }
}
