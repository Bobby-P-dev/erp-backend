<?php

namespace Tests\Feature\Purchasing;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\Purchasing\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected Supplier $supplier1;

    protected Supplier $supplier2;

    protected Supplier $inactiveSupplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'code' => 'PSI',
            'name' => 'PT Padma Soode Indonesia',
            'is_active' => true,
        ]);

        $division = Division::create([
            'company_id' => $this->company->id,
            'code' => 'PUR',
            'name' => 'Purchasing',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'company_id' => $this->company->id,
            'division_id' => $division->id,
            'nik' => 'EMP001',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'employee_id' => $employee->id,
            'account_type' => 'employee',
            'password' => bcrypt('password'),
        ]);

        $this->supplier1 = Supplier::create([
            'company_id' => $this->company->id,
            'supplier_code' => 'SUP-001',
            'name' => 'PT Mitra Sejahtera',
            'email' => 'mitra@example.com',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $this->supplier2 = Supplier::create([
            'company_id' => $this->company->id,
            'supplier_code' => 'SUP-002',
            'name' => 'CV Sumber Makmur',
            'email' => 'sumber@example.com',
            'phone' => '08987654321',
            'is_active' => true,
        ]);

        $this->inactiveSupplier = Supplier::create([
            'company_id' => $this->company->id,
            'supplier_code' => 'SUP-003',
            'name' => 'PT Mitra Nonaktif',
            'email' => 'nonaktif@example.com',
            'is_active' => false,
        ]);
    }

    public function test_can_search_suppliers_and_selects_only_id_and_name(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/supplier/search');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Supplier search results fetched successfully')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name'],
                ],
                'message',
            ]);

        // Verify only id and name are present in each item
        $data = $response->json('data');
        $this->assertCount(2, $data);

        foreach ($data as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayNotHasKey('email', $item);
            $this->assertArrayNotHasKey('phone', $item);
            $this->assertArrayNotHasKey('supplier_code', $item);
        }
    }

    public function test_can_filter_suppliers_by_keyword(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/supplier/search?search=Mitra');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->supplier1->id)
            ->assertJsonPath('data.0.name', 'PT Mitra Sejahtera');
    }

    public function test_inactive_suppliers_are_excluded(): void
    {
        Sanctum::actingAs($this->user);

        // Even when searching specifically for the inactive supplier name
        $response = $this->getJson('/api/v1/supplier/search?search=Nonaktif');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_unauthenticated_user_cannot_access_supplier_search(): void
    {
        $response = $this->getJson('/api/v1/supplier/search');

        $response->assertStatus(401);
    }
}
