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

    public function test_unauthenticated_user_cannot_access_suppliers(): void
    {
        $response = $this->getJson('/api/v1/suppliers');

        $response->assertStatus(401);
    }

    public function test_can_list_suppliers_with_pagination(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/suppliers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'supplier_code', 'is_active'],
                ],
                'meta',
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_filter_suppliers_list_by_active_and_search(): void
    {
        Sanctum::actingAs($this->user);

        $activeResponse = $this->getJson('/api/v1/suppliers?is_active=1');
        $activeResponse->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $searchResponse = $this->getJson('/api/v1/suppliers?search=Sumber');
        $searchResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'CV Sumber Makmur');
    }

    public function test_can_create_supplier(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'company_id' => $this->company->id,
            'supplier_code' => 'SUP-999',
            'name' => 'PT Vendor Baru Indonesia',
            'supplier_type' => 'Distributor',
            'bussines_type' => 'PT',
            'company_category' => 'Raw Material',
            'email' => 'vendor.baru@example.com',
            'phone' => '021-998877',
            'payment_term' => 'Net 30',
            'lead_time_days' => 7,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/suppliers', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.supplier_code', 'SUP-999')
            ->assertJsonPath('data.name', 'PT Vendor Baru Indonesia')
            ->assertJsonPath('message', 'Supplier created successfully');

        $this->assertDatabaseHas('suppliers', [
            'supplier_code' => 'SUP-999',
            'name' => 'PT Vendor Baru Indonesia',
        ]);
    }

    public function test_create_supplier_generates_code_if_omitted(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'name' => 'PT Auto Code Supplier',
            'email' => 'autocode@example.com',
        ];

        $response = $this->postJson('/api/v1/suppliers', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'PT Auto Code Supplier');

        $code = $response->json('data.supplier_code');
        $this->assertNotNull($code);
        $this->assertStringStartsWith('SUP-', $code);

        $this->assertDatabaseHas('suppliers', [
            'supplier_code' => $code,
            'name' => 'PT Auto Code Supplier',
        ]);
    }

    public function test_validation_fails_when_creating_supplier_without_name(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/suppliers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_show_supplier(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/suppliers/'.$this->supplier1->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->supplier1->id)
            ->assertJsonPath('data.supplier_code', 'SUP-001')
            ->assertJsonPath('data.name', 'PT Mitra Sejahtera');
    }

    public function test_show_supplier_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/suppliers/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_supplier(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/v1/suppliers/'.$this->supplier1->id, [
            'name' => 'PT Mitra Sejahtera Updated',
            'phone' => '0899999999',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'PT Mitra Sejahtera Updated')
            ->assertJsonPath('data.phone', '0899999999');

        $this->assertDatabaseHas('suppliers', [
            'id' => $this->supplier1->id,
            'name' => 'PT Mitra Sejahtera Updated',
            'phone' => '0899999999',
        ]);
    }

    public function test_can_delete_supplier(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/suppliers/'.$this->supplier1->id);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Supplier deleted successfully');

        $this->assertSoftDeleted('suppliers', [
            'id' => $this->supplier1->id,
        ]);
    }

    public function test_cannot_create_supplier_with_duplicate_code(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/suppliers', [
            'name' => 'Supplier Duplicate Code',
            'supplier_code' => 'SUP-001', // already used by supplier1
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_code']);
    }

    public function test_cannot_update_supplier_with_duplicate_code(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/v1/suppliers/'.$this->supplier2->id, [
            'supplier_code' => 'SUP-001', // already used by supplier1
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_code']);
    }

    public function test_update_supplier_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/v1/suppliers/99999', [
            'name' => 'Non Existent Supplier',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_supplier_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/suppliers/99999');

        $response->assertStatus(404);
    }
}
