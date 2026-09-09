<?php

namespace Database\Seeders;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\DocumentNumberSequence;
use App\Models\Core\Employee;
use App\Models\Purchasing\Item;
use App\Models\Purchasing\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PurchasingMasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Company
        $company = Company::firstOrCreate(
            ['code' => 'PSI'],
            [
                'name' => 'PT Padma Soode Indonesia',
                'is_active' => true,
            ]
        );

        // 2. Division (Production, Purchasing, PPIC)
        $production = Division::firstOrCreate(
            [
                'company_id' => $company->id,
                'code' => 'PROD',
            ],
            [
                'name' => 'Production',
                'is_active' => true,
            ]
        );

        $purchasing = Division::firstOrCreate(
            [
                'company_id' => $company->id,
                'code' => 'PUR',
            ],
            [
                'name' => 'Purchasing',
                'is_active' => true,
            ]
        );

        $ppic = Division::firstOrCreate(
            [
                'company_id' => $company->id,
                'code' => 'PPIC',
            ],
            [
                'name' => 'PPIC',
                'is_active' => true,
            ]
        );

        // 3. Employee (linked to Company and Division)
        $employee = Employee::firstOrCreate(
            [
                'company_id' => $company->id,
                'nik' => 'EMP-PUR-001',
            ],
            [
                'division_id' => $purchasing->id,
                'position_id' => null,
                'job_level_id' => null,
                'name' => 'Staff Purchasing',
                'email' => 'purchasing.staff@padmasoode.co.id',
                'is_active' => true,
            ]
        );

        // 4. User (linked to Employee via employee_id; note User table does not have name/email)
        $user = User::firstOrCreate(
            ['employee_id' => $employee->id],
            [
                'password' => Hash::make('password'),
            ]
        );

        // 5. Units (PCS, KG, METER)
        $unitPcs = Unit::firstOrCreate(
            ['code' => 'PCS'],
            ['name' => 'PCS']
        );

        $unitKg = Unit::firstOrCreate(
            ['code' => 'KG'],
            ['name' => 'KG']
        );

        $unitMeter = Unit::firstOrCreate(
            ['code' => 'METER'],
            ['name' => 'METER']
        );

        // 6. Items (ITEM-001, ITEM-002, ITEM-003)
        Item::firstOrCreate(
            ['code' => 'ITEM-001'],
            [
                'name' => 'Baut',
                'description' => 'Baut ukuran standar',
                'item_type' => 'Raw Material',
                'unit_id' => $unitPcs->id,
            ]
        );

        Item::firstOrCreate(
            ['code' => 'ITEM-002'],
            [
                'name' => 'Plat Besi',
                'description' => 'Plat besi lembaran',
                'item_type' => 'Raw Material',
                'unit_id' => $unitKg->id,
            ]
        );

        Item::firstOrCreate(
            ['code' => 'ITEM-003'],
            [
                'name' => 'Material Produksi',
                'description' => 'Material pendukung proses produksi',
                'item_type' => 'Raw Material',
                'unit_id' => $unitMeter->id,
            ]
        );

        // 7. DocumentNumberSequence for Purchase Requisition (Category 'PR')
        $currentYear = (string) date('Y');
        DocumentNumberSequence::firstOrCreate(
            [
                'company_id' => $company->id,
                'category' => 'PR',
                'period' => $currentYear,
            ],
            [
                'prefix' => 'PR',
                'current_number' => 0,
                'format' => '{prefix}-{period}-{number}',
                'number_length' => 6,
                'is_active' => true,
            ]
        );
    }
}
