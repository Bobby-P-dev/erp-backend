<?php

namespace Tests\Feature\Purchasing;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\Purchasing\Item;
use App\Models\Purchasing\Supplier;
use App\Models\Purchasing\SupplierItem;
use App\Models\Purchasing\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupplierItemControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected Supplier $supplier1;

    protected Supplier $supplier2;

    protected Item $item1;

    protected Item $item2;

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

        $unit = Unit::create([
            'code' => 'PCS',
            'name' => 'Pieces',
        ]);

        $this->item1 = Item::create([
            'code' => 'ITM-001',
            'name' => 'Baut Hexagonal',
            'description' => 'Baut baja hexagonal M8',
            'item_type' => 'Raw Material',
            'unit_id' => $unit->id,
        ]);

        $this->item2 = Item::create([
            'code' => 'ITM-002',
            'name' => 'Mur Hexagonal',
            'description' => 'Mur baja hexagonal M8',
            'item_type' => 'Raw Material',
            'unit_id' => $unit->id,
        ]);
    }

    public function test_can_list_supplier_items_with_pagination(): void
    {
        Sanctum::actingAs($this->user);

        SupplierItem::create([
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'SUP-ITM-001',
            'supplier_item_name' => 'Baut M8 Mitra',
            'default_price' => 1500.00,
            'currency' => 'IDR',
            'minimum_order_quantity' => 100,
            'lead_time_days' => 7,
            'is_active' => true,
        ]);

        SupplierItem::create([
            'supplier_id' => $this->supplier2->id,
            'item_id' => $this->item2->id,
            'supplier_item_code' => 'SUP-ITM-002',
            'supplier_item_name' => 'Mur M8 Sumber',
            'default_price' => 1200.00,
            'currency' => 'IDR',
            'minimum_order_quantity' => 200,
            'lead_time_days' => 5,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/supplier-items?per_page=10');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Supplier items retrieved successfully')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'supplier_id',
                        'supplier',
                        'item_id',
                        'item',
                        'supplier_item_code',
                        'supplier_item_name',
                        'reference_url',
                        'default_price',
                        'currency',
                        'minimum_order_quantity',
                        'lead_time_days',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta',
                'links',
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_filter_supplier_items_by_supplier_id(): void
    {
        Sanctum::actingAs($this->user);

        SupplierItem::create([
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'SUP-ITM-001',
            'default_price' => 1500.00,
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        SupplierItem::create([
            'supplier_id' => $this->supplier2->id,
            'item_id' => $this->item2->id,
            'supplier_item_code' => 'SUP-ITM-002',
            'default_price' => 1200.00,
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/supplier-items?supplier_id='.$this->supplier1->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.supplier_id', $this->supplier1->id);
    }

    public function test_can_filter_supplier_items_by_is_active(): void
    {
        Sanctum::actingAs($this->user);

        SupplierItem::create([
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'SUP-ITM-001',
            'is_active' => true,
        ]);

        SupplierItem::create([
            'supplier_id' => $this->supplier2->id,
            'item_id' => $this->item2->id,
            'supplier_item_code' => 'SUP-ITM-002',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/supplier-items?is_active=false');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.supplier_item_code', 'SUP-ITM-002');
    }

    public function test_can_search_supplier_items_by_keyword(): void
    {
        Sanctum::actingAs($this->user);

        SupplierItem::create([
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'SUP-ITM-BAUT',
            'supplier_item_name' => 'Baut M8 Khusus',
            'is_active' => true,
        ]);

        SupplierItem::create([
            'supplier_id' => $this->supplier2->id,
            'item_id' => $this->item2->id,
            'supplier_item_code' => 'SUP-ITM-MUR',
            'supplier_item_name' => 'Mur M8 Standar',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/supplier-items?search=Khusus');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.supplier_item_code', 'SUP-ITM-BAUT');
    }

    public function test_can_create_supplier_item(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'SUP-BAUT-01',
            'supplier_item_name' => 'Baut Grade A',
            'reference_url' => 'https://example.com/item/baut-01',
            'default_price' => 2500.50,
            'currency' => 'IDR',
            'minimum_order_quantity' => 50,
            'lead_time_days' => 10,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/supplier-items', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Supplier item created successfully')
            ->assertJsonPath('data.supplier_id', $this->supplier1->id)
            ->assertJsonPath('data.item_id', $this->item1->id)
            ->assertJsonPath('data.supplier_item_code', 'SUP-BAUT-01')
            ->assertJsonPath('data.default_price', 2500.5);

        $this->assertDatabaseHas('supplier_items', [
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'SUP-BAUT-01',
        ]);
    }

    public function test_cannot_create_duplicate_supplier_item_for_same_supplier(): void
    {
        Sanctum::actingAs($this->user);

        SupplierItem::create([
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'EXISTING-01',
            'is_active' => true,
        ]);

        $payload = [
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'ANOTHER-CODE',
        ];

        $response = $this->postJson('/api/v1/supplier-items', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_validates_required_fields_when_creating_supplier_item(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/supplier-items', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_id', 'item_id']);
    }

    public function test_can_show_supplier_item(): void
    {
        Sanctum::actingAs($this->user);

        $supplierItem = SupplierItem::create([
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'SHOW-ITM-01',
            'supplier_item_name' => 'Detail Item',
            'default_price' => 999.00,
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/supplier-items/{$supplierItem->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Supplier item retrieved successfully')
            ->assertJsonPath('data.id', $supplierItem->id)
            ->assertJsonPath('data.supplier.id', $this->supplier1->id)
            ->assertJsonPath('data.item.id', $this->item1->id);
    }

    public function test_returns_404_when_supplier_item_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/supplier-items/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_supplier_item(): void
    {
        Sanctum::actingAs($this->user);

        $supplierItem = SupplierItem::create([
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'BEFORE-UPDATE',
            'default_price' => 1000.00,
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $updatePayload = [
            'supplier_item_code' => 'AFTER-UPDATE',
            'default_price' => 1500.00,
            'is_active' => false,
        ];

        $response = $this->patchJson("/api/v1/supplier-items/{$supplierItem->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Supplier item updated successfully')
            ->assertJsonPath('data.supplier_item_code', 'AFTER-UPDATE')
            ->assertJsonPath('data.default_price', 1500)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('supplier_items', [
            'id' => $supplierItem->id,
            'supplier_item_code' => 'AFTER-UPDATE',
            'is_active' => false,
        ]);
    }

    public function test_can_delete_supplier_item(): void
    {
        Sanctum::actingAs($this->user);

        $supplierItem = SupplierItem::create([
            'supplier_id' => $this->supplier1->id,
            'item_id' => $this->item1->id,
            'supplier_item_code' => 'TO-DELETE',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/v1/supplier-items/{$supplierItem->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Supplier item deleted successfully');

        $this->assertSoftDeleted('supplier_items', [
            'id' => $supplierItem->id,
        ]);
    }

    public function test_update_supplier_item_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/v1/supplier-items/99999', [
            'default_price' => 5000,
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_supplier_item_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/supplier-items/99999');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_access_supplier_items(): void
    {
        $response = $this->getJson('/api/v1/supplier-items');

        $response->assertStatus(401);
    }
}
