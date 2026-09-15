<?php

namespace Tests\Feature\Core;

use App\Models\Core\AccountingAccount;
use App\Models\Core\AccountingCategory;
use App\Models\Core\AccountingSubcategory;
use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountingAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected AccountingSubcategory $subcategory;

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

        $category = AccountingCategory::create([
            'code' => '1',
            'name' => 'Assets',
            'is_active' => true,
        ]);

        $this->subcategory = AccountingSubcategory::create([
            'accounting_category_id' => $category->id,
            'code' => '11',
            'name' => 'Current Assets',
            'is_active' => true,
        ]);
    }

    public function test_can_get_all_accounting_accounts(): void
    {
        Sanctum::actingAs($this->user);

        AccountingAccount::create([
            'accounting_subcategory_id' => $this->subcategory->id,
            'code' => '1101',
            'name' => 'Kas Kecil',
            'is_active' => true,
        ]);

        AccountingAccount::create([
            'accounting_subcategory_id' => $this->subcategory->id,
            'code' => '1102',
            'name' => 'Bank BCA',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/accounting-account/get-all');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting account list fetched successfully')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'accounting_subcategory_id',
                        'subcategory',
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

    public function test_can_filter_accounts_by_subcategory_id(): void
    {
        Sanctum::actingAs($this->user);

        $sub2 = AccountingSubcategory::create([
            'accounting_category_id' => $this->subcategory->accounting_category_id,
            'code' => '12',
            'name' => 'Fixed Assets',
        ]);

        AccountingAccount::create([
            'accounting_subcategory_id' => $this->subcategory->id,
            'code' => '1101',
            'name' => 'Kas Kecil',
        ]);

        AccountingAccount::create([
            'accounting_subcategory_id' => $sub2->id,
            'code' => '1201',
            'name' => 'Tanah & Bangunan',
        ]);

        $response = $this->getJson('/api/v1/accounting-account/get-all?accounting_subcategory_id='.$this->subcategory->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.accounting_subcategory_id', $this->subcategory->id);
    }

    public function test_can_search_accounting_accounts(): void
    {
        Sanctum::actingAs($this->user);

        AccountingAccount::create([
            'accounting_subcategory_id' => $this->subcategory->id,
            'code' => '1101',
            'name' => 'Kas Kecil',
        ]);

        AccountingAccount::create([
            'accounting_subcategory_id' => $this->subcategory->id,
            'code' => '1102',
            'name' => 'Bank Mandiri',
        ]);

        $response = $this->getJson('/api/v1/accounting-account/search?search=Mandiri');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting account search fetched successfully')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', '1102');
    }

    public function test_can_store_accounting_account(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'accounting_subcategory_id' => $this->subcategory->id,
            'code' => '1103',
            'name' => 'Bank BNI',
            'description' => 'Rekening Operasional BNI',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/accounting-account/store', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Accounting account created successfully')
            ->assertJsonPath('data.accounting_subcategory_id', $this->subcategory->id)
            ->assertJsonPath('data.code', '1103')
            ->assertJsonPath('data.name', 'Bank BNI');

        $this->assertDatabaseHas('accounting_accounts', [
            'accounting_subcategory_id' => $this->subcategory->id,
            'code' => '1103',
        ]);
    }

    public function test_validates_required_fields_on_store(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/accounting-account/store', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['accounting_subcategory_id', 'code', 'name']);
    }

    public function test_can_show_accounting_account(): void
    {
        Sanctum::actingAs($this->user);

        $acc = AccountingAccount::create([
            'accounting_subcategory_id' => $this->subcategory->id,
            'code' => '1101',
            'name' => 'Kas Kecil',
        ]);

        $response = $this->getJson("/api/v1/accounting-account/{$acc->id}/show");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting account details fetched successfully')
            ->assertJsonPath('data.id', $acc->id)
            ->assertJsonPath('data.subcategory.id', $this->subcategory->id);
    }

    public function test_returns_404_when_showing_nonexistent_account(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/accounting-account/99999/show');

        $response->assertStatus(404);
    }

    public function test_can_update_accounting_account(): void
    {
        Sanctum::actingAs($this->user);

        $acc = AccountingAccount::create([
            'accounting_subcategory_id' => $this->subcategory->id,
            'code' => '1101',
            'name' => 'Old Name',
        ]);

        $response = $this->patchJson("/api/v1/accounting-account/{$acc->id}/update", [
            'name' => 'Updated Account Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting account updated successfully')
            ->assertJsonPath('data.name', 'Updated Account Name');

        $this->assertDatabaseHas('accounting_accounts', [
            'id' => $acc->id,
            'name' => 'Updated Account Name',
        ]);
    }

    public function test_can_delete_accounting_account(): void
    {
        Sanctum::actingAs($this->user);

        $acc = AccountingAccount::create([
            'accounting_subcategory_id' => $this->subcategory->id,
            'code' => '1101',
            'name' => 'To Delete',
        ]);

        $response = $this->deleteJson("/api/v1/accounting-account/{$acc->id}/delete");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Accounting account deleted successfully');

        $this->assertSoftDeleted('accounting_accounts', [
            'id' => $acc->id,
        ]);
    }

    public function test_validates_nonexistent_subcategory_id_on_store(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/accounting-account/store', [
            'accounting_subcategory_id' => 99999,
            'code' => '1103',
            'name' => 'Account Invalid Subcategory',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['accounting_subcategory_id']);
    }

    public function test_returns_404_when_updating_nonexistent_account(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/v1/accounting-account/99999/update', [
            'name' => 'Non Existent',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Accounting account not found');
    }

    public function test_returns_404_when_deleting_nonexistent_account(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/accounting-account/99999/delete');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Accounting account not found');
    }

    public function test_unauthenticated_user_cannot_access_accounting_accounts(): void
    {
        $response = $this->getJson('/api/v1/accounting-account/get-all');

        $response->assertStatus(401);
    }
}
