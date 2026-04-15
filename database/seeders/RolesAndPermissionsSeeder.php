<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    private const string SUPER_ADMIN_ROLE = 'SuperAdmin';

    private const string SUPER_ADMIN_EMAIL = 'superadmin@procurement.test';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'vendor.manage',
            'vendor.approve',
            'rfq.create',
            'rfq.view',
            'rfq.update',
            'rfq.delete',
            'rfq.respond',
            'po.create',
            'po.view',
            'po.update',
            'po.delete',
            'gr.create',
            'invoice.view',
            'invoice.upload',
            'invoice.approve',
            'report.vendor.summary',
            'user.manage',
            'permission.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdminRole = Role::findOrCreate(self::SUPER_ADMIN_ROLE, 'web');
        $adminRole = Role::findOrCreate('Admin', 'web');
        $procurementRole = Role::findOrCreate('Procurement', 'web');
        $vendorRole = Role::findOrCreate('Vendor', 'web');

        $superAdminRole->syncPermissions($permissions);
        $adminRole->syncPermissions($permissions);

        $procurementRole->syncPermissions([
            'vendor.manage',
            'rfq.create',
            'rfq.view',
            'rfq.update',
            'rfq.delete',
            'po.create',
            'po.view',
            'po.update',
            'po.delete',
            'gr.create',
            'invoice.view',
            'report.vendor.summary',
        ]);

        $vendorRole->syncPermissions([
            'rfq.view',
            'rfq.respond',
            'po.view',
            'invoice.view',
            'invoice.upload',
        ]);

        $adminUser = User::query()->firstOrCreate(
            ['email' => 'admin@procurement.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $superAdminUser = User::query()->firstOrCreate(
            ['email' => self::SUPER_ADMIN_EMAIL],
            [
                'name' => 'superadmin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $procurementUser = User::query()->firstOrCreate(
            ['email' => 'procurement@procurement.test'],
            [
                'name' => 'Procurement User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $superAdminUser->syncRoles([$superAdminRole]);
        $adminUser->syncRoles([$adminRole]);
        $procurementUser->syncRoles([$procurementRole]);
    }
}
