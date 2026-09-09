<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        if (! DB::table('companies')->where('code', 'C01')->exists()) {
            DB::table('companies')->insert([
                ['code' => 'C01', 'name' => 'Company A', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'C02', 'name' => 'Company B', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'C03', 'name' => 'Company C', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('divisions')->insert([
                ['company_id' => 1, 'code' => 'D01', 'name' => 'Division A', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['company_id' => 2, 'code' => 'D02', 'name' => 'Division B', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['company_id' => 3, 'code' => 'D03', 'name' => 'Division C', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('positions')->insert([
                ['code' => 'P01', 'name' => 'Position A', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'P02', 'name' => 'Position B', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'P03', 'name' => 'Position C', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('employees')->insert([
                ['company_id' => 1, 'division_id' => 1, 'position_id' => 1, 'nik' => 'E001', 'name' => 'Employee A', 'email' => 'emp1@test.com', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['company_id' => 2, 'division_id' => 2, 'position_id' => 2, 'nik' => 'E002', 'name' => 'Employee B', 'email' => 'emp2@test.com', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['company_id' => 3, 'division_id' => 3, 'position_id' => 3, 'nik' => 'E003', 'name' => 'Employee C', 'email' => 'emp3@test.com', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);

            $password = Hash::make('password');
            DB::table('users')->insert([
                ['employee_id' => 1, 'password' => $password, 'created_at' => $now, 'updated_at' => $now],
                ['employee_id' => 2, 'password' => $password, 'created_at' => $now, 'updated_at' => $now],
                ['employee_id' => 3, 'password' => $password, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        $this->call([
            PurchasingMasterDataSeeder::class,
            SupplierSeeder::class,
        ]);
    }
}
