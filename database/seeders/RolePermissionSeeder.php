<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign created permissions

        // 1. Super Admin: gets all permissions
        $roleSuperAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin']);
        $roleSuperAdmin->givePermissionTo(\Spatie\Permission\Models\Permission::all());

        // 2. Admin: gets specific permissions
        $roleAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        $roleAdmin->givePermissionTo([
            'view dashboard',
            'view users',
            'create users',
            'edit users',
        ]);

        // 3. Staff: gets limited permissions
        $roleStaff = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Staff']);
        $roleStaff->givePermissionTo([
            'view dashboard',
        ]);
    }
}
