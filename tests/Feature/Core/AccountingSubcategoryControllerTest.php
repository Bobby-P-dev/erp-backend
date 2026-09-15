<?php

namespace Tests\Feature\Core;

use App\Models\Core\AccountingCategory;
use App\Models\Core\AccountingSubcategory;
use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountingSubcategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected AccountingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $company = Company::create([
            'code' => 'PSI',
            'name' => 'PT Padma Soode Indonesia',
            'is_active' => true,
        ]);

        $division = Division::create([
            'company_id' => $company->id,
            'code' => 'ACC',
            'name' => 'Accounting',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'company_id' => $company->id,
            'division_id' => $division->id,
            'nik' => 'ACC001',
            'name' => 'Finance Staff',
            'email' => 'finance@example.com',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'employee_id' => $employee->id,
            'account_type' => 'employee',
            'password' => bcrypt('password'),
        ]);

        $this->category = AccountingCategory::create([
            'code' => '1',
            'name' => 'Assets',
            'is_active' => true,
        ]);
    }

    public function test_can_get_all_accounting_subcategories(): void
    {
        Sanctum::actingAs($this->user);

        AccountingSubcategory::create([
            'accounting_category_id' => $this->category->id,
            'code' => '11',
            'name' => 'Current Assets',
            'is_active' => true,
        ]);

        AccountingSubcategory::create([
            'accounting_category_id' => $this->category->id,
            'code' => '12',
            'name' => 'Fixed Assets',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/accounting-subcategory/get-all');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting subcategory list fetched successfully')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'accounting_category_id',
                        'category',
                        'code',
                        'name',
                        'description',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_filter_subcategories_by_category_id(): void
    {
        Sanctum::actingAs($this->user);

        $cat2 = AccountingCategory::create(['code' => '2', 'name' => 'Liabilities']);

        AccountingSubcategory::create([
            'accounting_category_id' => $this->category->id,
            'code' => '11',
            'name' => 'Current Assets',
        ]);

        AccountingSubcategory::create([
            'accounting_category_id' => $cat2->id,
            'code' => '21',
            'name' => 'Short Term Debt',
        ]);

        $response = $this->getJson('/api/v1/accounting-subcategory/get-all?accounting_category_id='.$this->category->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.accounting_category_id', $this->category->id);
    }

    public function test_can_search_accounting_subcategories(): void
    {
        Sanctum::actingAs($this->user);

        AccountingSubcategory::create([
            'accounting_category_id' => $this->category->id,
            'code' => '11',
            'name' => 'Current Assets',
        ]);

        AccountingSubcategory::create([
            'accounting_category_id' => $this->category->id,
            'code' => '12',
            'name' => 'Fixed Assets',
        ]);

        $response = $this->getJson('/api/v1/accounting-subcategory/search?search=Fixed');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting subcategory search fetched successfully')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', '12');
    }

    public function test_can_store_accounting_subcategory(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'accounting_category_id' => $this->category->id,
            'code' => '11',
            'name' => 'Kas & Setara Kas',
            'description' => 'Subkategori Kas',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/accounting-subcategory/store', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Accounting subcategory created successfully')
            ->assertJsonPath('data.accounting_category_id', $this->category->id)
            ->assertJsonPath('data.code', '11')
            ->assertJsonPath('data.name', 'Kas & Setara Kas');

        $this->assertDatabaseHas('accounting_subcategories', [
            'accounting_category_id' => $this->category->id,
            'code' => '11',
        ]);
    }

    public function test_validates_required_fields_on_store(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/accounting-subcategory/store', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['accounting_category_id', 'code', 'name']);
    }

    public function test_can_show_accounting_subcategory(): void
    {
        Sanctum::actingAs($this->user);

        $sub = AccountingSubcategory::create([
            'accounting_category_id' => $this->category->id,
            'code' => '11',
            'name' => 'Current Assets',
        ]);

        $response = $this->getJson("/api/v1/accounting-subcategory/{$sub->id}/show");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting subcategory details fetched successfully')
            ->assertJsonPath('data.id', $sub->id)
            ->assertJsonPath('data.category.id', $this->category->id);
    }

    public function test_returns_404_when_showing_nonexistent_subcategory(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting-subcategory/99999/show');

        $response->assertStatus(404);
    }

    public function test_can_update_accounting_subcategory(): void
    {
        Sanctum::actingAs($this->user);

        $sub = AccountingSubcategory::create([
            'accounting_category_id' => $this->category->id,
            'code' => '11',
            'name' => 'Old Name',
        ]);

        $response = $this->patchJson("/api/v1/accounting-subcategory/{$sub->id}/update", [
            'name' => 'Updated Sub Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting subcategory updated successfully')
            ->assertJsonPath('data.name', 'Updated Sub Name');

        $this->assertDatabaseHas('accounting_subcategories', [
            'id' => $sub->id,
            'name' => 'Updated Sub Name',
        ]);
    }

    public function test_can_delete_accounting_subcategory(): void
    {
        Sanctum::actingAs($this->user);

        $sub = AccountingSubcategory::create([
            'accounting_category_id' => $this->category->id,
            'code' => '11',
            'name' => 'To Delete',
        ]);

        $response = $this->deleteJson("/api/v1/accounting-subcategory/{$sub->id}/delete");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting subcategory deleted successfully');

        $this->assertSoftDeleted('accounting_subcategories', [
            'id' => $sub->id,
        ]);
    }

    public function test_validates_nonexistent_category_id_on_store(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/accounting-subcategory/store', [
            'accounting_category_id' => 99999,
            'code' => '11',
            'name' => 'Subcategory Invalid Category',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['accounting_category_id']);
    }

    public function test_returns_404_when_updating_nonexistent_subcategory(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/v1/accounting-subcategory/99999/update', [
            'name' => 'Non Existent',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Accounting subcategory not found');
    }

    public function test_returns_404_when_deleting_nonexistent_subcategory(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/accounting-subcategory/99999/delete');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Accounting subcategory not found');
    }

    public function test_unauthenticated_user_cannot_access_accounting_subcategories(): void
    {
        $response = $this->getJson('/api/v1/accounting-subcategory/get-all');

        $response->assertStatus(401);
    }
}
