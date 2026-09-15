<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign created permissions

        // 1. Super Admin: gets all permissions
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $roleSuperAdmin->givePermissionTo(Permission::all());

        // 2. Admin: gets specific permissions
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleAdmin->givePermissionTo([
            'view dashboard',
            'view users',
            'create users',
            'edit users',
        ]);

        // 3. Staff: gets limited permissions
        $roleStaff = Role::firstOrCreate(['name' => 'Staff']);
        $roleStaff->givePermissionTo([
            'view dashboard',
        ]);
    }
}
