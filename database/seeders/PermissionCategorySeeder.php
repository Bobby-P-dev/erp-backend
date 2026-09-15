<?php

namespace Database\Seeders;

use App\Models\Core\PermissionCategory;
use Illuminate\Database\Seeder;

class PermissionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Users',
            'Roles',
            'Permissions',
            'Companies',
            'Divisions',
            'Positions',
            'Employees',
        ];

        foreach ($categories as $category) {
            PermissionCategory::firstOrCreate(['name' => $category]);
        }
    }
}
