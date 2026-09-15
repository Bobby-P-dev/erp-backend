<?php

namespace Tests\Feature\Purchasing;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\Purchasing\Supplier;
use App\Models\Purchasing\SupplierBankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupplierBankAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected Supplier $supplier;

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

        $this->supplier = Supplier::create([
            'company_id' => $this->company->id,
            'supplier_code' => 'SUP-001',
            'name' => 'PT Mitra Sejahtera',
            'email' => 'mitra@example.com',
            'phone' => '08123456789',
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_bank_accounts(): void
    {
        $response = $this->getJson('/api/v1/supplier-bank-accounts');
        $response->assertStatus(401);
    }

    public function test_can_list_supplier_bank_accounts(): void
    {
        Sanctum::actingAs($this->user);

        SupplierBankAccount::create([
            'supplier_id' => $this->supplier->id,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'PT Mitra Sejahtera',
            'is_primary' => true,
        ]);

        $response = $this->getJson('/api/v1/supplier-bank-accounts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'supplier_id', 'bank_name', 'bank_account_number', 'bank_account_name', 'is_primary'],
                ],
                'meta',
            ]);
    }

    public function test_can_filter_and_search_supplier_bank_accounts(): void
    {
        Sanctum::actingAs($this->user);

        SupplierBankAccount::create([
            'supplier_id' => $this->supplier->id,
            'bank_name' => 'Bank Mandiri',
            'bank_account_number' => '111222333',
            'bank_account_name' => 'PT Mitra Sejahtera',
            'is_primary' => true,
        ]);

        SupplierBankAccount::create([
            'supplier_id' => $this->supplier->id,
            'bank_name' => 'BCA',
            'bank_account_number' => '999888777',
            'bank_account_name' => 'PT Mitra Sejahtera',
            'is_primary' => false,
        ]);

        $primaryResponse = $this->getJson('/api/v1/supplier-bank-accounts?is_primary=1');
        $primaryResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.bank_name', 'Bank Mandiri');

        $searchResponse = $this->getJson('/api/v1/supplier-bank-accounts?search=999888');
        $searchResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.bank_name', 'BCA');
    }

    public function test_can_create_supplier_bank_account_and_resets_primary(): void
    {
        Sanctum::actingAs($this->user);

        $firstAccount = SupplierBankAccount::create([
            'supplier_id' => $this->supplier->id,
            'bank_name' => 'BCA',
            'bank_account_number' => '1111111111',
            'bank_account_name' => 'PT Mitra Sejahtera',
            'is_primary' => true,
        ]);

        $payload = [
            'supplier_id' => $this->supplier->id,
            'bank_name' => 'BNI',
            'bank_account_number' => '2222222222',
            'bank_account_name' => 'PT Mitra Sejahtera',
            'branch' => 'Jakarta Sudirman',
            'is_primary' => true,
        ];

        $response = $this->postJson('/api/v1/supplier-bank-accounts', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.bank_name', 'BNI')
            ->assertJsonPath('data.is_primary', true);

        $this->assertDatabaseHas('supplier_bank_accounts', [
            'bank_name' => 'BNI',
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('supplier_bank_accounts', [
            'id' => $firstAccount->id,
            'is_primary' => false,
        ]);
    }

    public function test_validation_fails_when_bank_account_fields_missing(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/supplier-bank-accounts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_id', 'bank_name', 'bank_account_number', 'bank_account_name']);
    }

    public function test_can_show_supplier_bank_account(): void
    {
        Sanctum::actingAs($this->user);

        $account = SupplierBankAccount::create([
            'supplier_id' => $this->supplier->id,
            'bank_name' => 'BRI',
            'bank_account_number' => '3333333333',
            'bank_account_name' => 'PT Mitra Sejahtera',
        ]);

        $response = $this->getJson('/api/v1/supplier-bank-accounts/'.$account->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $account->id)
            ->assertJsonPath('data.bank_name', 'BRI');

        $notFoundResponse = $this->getJson('/api/v1/supplier-bank-accounts/99999');
        $notFoundResponse->assertStatus(404);
    }

    public function test_can_update_supplier_bank_account(): void
    {
        Sanctum::actingAs($this->user);

        $account = SupplierBankAccount::create([
            'supplier_id' => $this->supplier->id,
            'bank_name' => 'CIMB',
            'bank_account_number' => '4444444444',
            'bank_account_name' => 'Old Name',
            'is_primary' => false,
        ]);

        $response = $this->patchJson('/api/v1/supplier-bank-accounts/'.$account->id, [
            'bank_account_name' => 'New Account Name',
            'is_primary' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.bank_account_name', 'New Account Name')
            ->assertJsonPath('data.is_primary', true);

        $this->assertDatabaseHas('supplier_bank_accounts', [
            'id' => $account->id,
            'bank_account_name' => 'New Account Name',
            'is_primary' => true,
        ]);
    }

    public function test_can_delete_supplier_bank_account(): void
    {
        Sanctum::actingAs($this->user);

        $account = SupplierBankAccount::create([
            'supplier_id' => $this->supplier->id,
            'bank_name' => 'Permata',
            'bank_account_number' => '5555555555',
            'bank_account_name' => 'PT Mitra Sejahtera',
        ]);

        $response = $this->deleteJson('/api/v1/supplier-bank-accounts/'.$account->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted('supplier_bank_accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_validates_nonexistent_supplier_id_on_create_bank_account(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/supplier-bank-accounts', [
            'supplier_id' => 99999,
            'bank_name' => 'BCA',
            'bank_account_number' => '12345678',
            'bank_account_name' => 'Invalid Supplier Account',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_id']);
    }

    public function test_update_bank_account_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/v1/supplier-bank-accounts/99999', [
            'bank_account_name' => 'Non Existent',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_bank_account_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/supplier-bank-accounts/99999');

        $response->assertStatus(404);
    }
}
