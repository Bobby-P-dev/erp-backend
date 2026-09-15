<?php

namespace Tests\Feature\Core;

use App\Models\Core\AccountingCategory;
use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountingCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

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
    }

    public function test_can_get_all_accounting_categories(): void
    {
        Sanctum::actingAs($this->user);

        AccountingCategory::create(['code' => '1', 'name' => 'Assets', 'is_active' => true]);
        AccountingCategory::create(['code' => '2', 'name' => 'Liabilities', 'is_active' => true]);

        $response = $this->getJson('/api/v1/accounting-category/get-all');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting category list fetched successfully')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'name', 'description', 'is_active', 'created_at', 'updated_at'],
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_search_accounting_categories(): void
    {
        Sanctum::actingAs($this->user);

        AccountingCategory::create(['code' => '1', 'name' => 'Current Assets', 'is_active' => true]);
        AccountingCategory::create(['code' => '2', 'name' => 'Non-Current Liabilities', 'is_active' => true]);

        $response = $this->getJson('/api/v1/accounting-category/search?search=Assets');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting category search fetched successfully')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', '1');
    }

    public function test_can_store_accounting_category(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'code' => '100',
            'name' => 'Aktiva Lancar',
            'description' => 'Kategori aktiva lancar',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/accounting-category/store', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Accounting category created successfully')
            ->assertJsonPath('data.code', '100')
            ->assertJsonPath('data.name', 'Aktiva Lancar');

        $this->assertDatabaseHas('accounting_categories', [
            'code' => '100',
            'name' => 'Aktiva Lancar',
        ]);
    }

    public function test_cannot_store_accounting_category_with_duplicate_code(): void
    {
        Sanctum::actingAs($this->user);

        AccountingCategory::create(['code' => '100', 'name' => 'First']);

        $response = $this->postJson('/api/v1/accounting-category/store', [
            'code' => '100',
            'name' => 'Second',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_validates_required_fields_on_store(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/accounting-category/store', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name']);
    }

    public function test_can_show_accounting_category(): void
    {
        Sanctum::actingAs($this->user);

        $cat = AccountingCategory::create(['code' => '1', 'name' => 'Assets']);

        $response = $this->getJson("/api/v1/accounting-category/{$cat->id}/show");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting category details fetched successfully')
            ->assertJsonPath('data.id', $cat->id)
            ->assertJsonPath('data.name', 'Assets');
    }

    public function test_returns_404_when_showing_nonexistent_category(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting-category/99999/show');

        $response->assertStatus(404);
    }

    public function test_can_update_accounting_category(): void
    {
        Sanctum::actingAs($this->user);

        $cat = AccountingCategory::create(['code' => '1', 'name' => 'Old Name']);

        $response = $this->patchJson("/api/v1/accounting-category/{$cat->id}/update", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting category updated successfully')
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('accounting_categories', [
            'id' => $cat->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_accounting_category(): void
    {
        Sanctum::actingAs($this->user);

        $cat = AccountingCategory::create(['code' => '1', 'name' => 'To Delete']);

        $response = $this->deleteJson("/api/v1/accounting-category/{$cat->id}/delete");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting category deleted successfully');

        $this->assertSoftDeleted('accounting_categories', [
            'id' => $cat->id,
        ]);
    }

    public function test_returns_404_when_updating_nonexistent_category(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/v1/accounting-category/99999/update', [
            'name' => 'Non Existent',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Accounting category not found');
    }

    public function test_returns_404_when_deleting_nonexistent_category(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/accounting-category/99999/delete');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Accounting category not found');
    }

    public function test_cannot_update_category_with_duplicate_code(): void
    {
        Sanctum::actingAs($this->user);

        AccountingCategory::create(['code' => '100', 'name' => 'First']);
        $second = AccountingCategory::create(['code' => '200', 'name' => 'Second']);

        $response = $this->patchJson("/api/v1/accounting-category/{$second->id}/update", [
            'code' => '100',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_unauthenticated_user_cannot_access_accounting_categories(): void
    {
        $response = $this->getJson('/api/v1/accounting-category/get-all');

        $response->assertStatus(401);
    }
}
