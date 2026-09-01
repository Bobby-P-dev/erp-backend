<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            \App\Models\Core\PermissionCategory::firstOrCreate(['name' => $category]);
        }
    }
}
