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
            'rfq.respond',
            'po.create',
            'po.view',
            'gr.create',
            'invoice.upload',
            'invoice.approve',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $adminRole = Role::findOrCreate('Admin', 'web');
        $procurementRole = Role::findOrCreate('Procurement', 'web');
        $vendorRole = Role::findOrCreate('Vendor', 'web');

        $adminRole->syncPermissions($permissions);

        $procurementRole->syncPermissions([
            'vendor.manage',
            'rfq.create',
            'rfq.view',
            'po.create',
            'po.view',
            'gr.create',
        ]);

        $vendorRole->syncPermissions([
            'rfq.view',
            'rfq.respond',
            'po.view',
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

        $procurementUser = User::query()->firstOrCreate(
            ['email' => 'procurement@procurement.test'],
            [
                'name' => 'Procurement User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $adminUser->syncRoles([$adminRole]);
        $procurementUser->syncRoles([$procurementRole]);
    }
}
