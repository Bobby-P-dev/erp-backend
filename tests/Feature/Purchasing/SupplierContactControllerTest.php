<?php

namespace Tests\Feature\Purchasing;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\Purchasing\Supplier;
use App\Models\Purchasing\SupplierContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupplierContactControllerTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_contacts(): void
    {
        $response = $this->getJson('/api/v1/supplier-contacts');
        $response->assertStatus(401);
    }

    public function test_can_list_supplier_contacts(): void
    {
        Sanctum::actingAs($this->user);

        SupplierContact::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '081111111',
            'title' => 'Manager',
            'is_primary' => true,
        ]);

        $response = $this->getJson('/api/v1/supplier-contacts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'supplier_id', 'name', 'email', 'phone', 'title', 'is_primary'],
                ],
                'meta',
            ]);
    }

    public function test_can_filter_contacts_by_supplier_and_search(): void
    {
        Sanctum::actingAs($this->user);

        $otherSupplier = Supplier::create([
            'company_id' => $this->company->id,
            'supplier_code' => 'SUP-002',
            'name' => 'CV Makmur Jaya',
            'is_active' => true,
        ]);

        SupplierContact::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'Alice Primary',
            'email' => 'alice@example.com',
            'phone' => '0812345',
            'title' => 'Director',
            'is_primary' => true,
        ]);

        SupplierContact::create([
            'supplier_id' => $otherSupplier->id,
            'name' => 'Bob Secondary',
            'email' => 'bob@example.com',
            'phone' => '0899999',
            'title' => 'Staff',
            'is_primary' => false,
        ]);

        $response = $this->getJson('/api/v1/supplier-contacts?supplier_id='.$this->supplier->id);
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Alice Primary');

        $searchResponse = $this->getJson('/api/v1/supplier-contacts?search=Bob');
        $searchResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Bob Secondary');
    }

    public function test_can_create_supplier_contact_and_resets_primary(): void
    {
        Sanctum::actingAs($this->user);

        $firstContact = SupplierContact::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'First Primary',
            'email' => 'first@example.com',
            'phone' => '08111',
            'title' => 'Supervisor',
            'is_primary' => true,
        ]);

        $payload = [
            'supplier_id' => $this->supplier->id,
            'name' => 'New Primary',
            'email' => 'new@example.com',
            'phone' => '08222',
            'title' => 'Sales Director',
            'is_primary' => true,
        ];

        $response = $this->postJson('/api/v1/supplier-contacts', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Primary')
            ->assertJsonPath('data.is_primary', true);

        $this->assertDatabaseHas('supplier_contacts', [
            'name' => 'New Primary',
            'is_primary' => true,
        ]);

        // Verify previous primary was reset
        $this->assertDatabaseHas('supplier_contacts', [
            'id' => $firstContact->id,
            'is_primary' => false,
        ]);
    }

    public function test_validation_fails_when_required_contact_fields_missing(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/supplier-contacts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_id', 'name', 'title']);
    }

    public function test_can_show_supplier_contact(): void
    {
        Sanctum::actingAs($this->user);

        $contact = SupplierContact::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'Contact Show',
            'email' => 'show@example.com',
            'phone' => '08333',
            'title' => 'Officer',
        ]);

        $response = $this->getJson('/api/v1/supplier-contacts/'.$contact->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $contact->id)
            ->assertJsonPath('data.name', 'Contact Show');

        $notFoundResponse = $this->getJson('/api/v1/supplier-contacts/99999');
        $notFoundResponse->assertStatus(404);
    }

    public function test_can_update_supplier_contact(): void
    {
        Sanctum::actingAs($this->user);

        $contact = SupplierContact::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'phone' => '08444',
            'title' => 'Officer',
            'is_primary' => false,
        ]);

        $response = $this->patchJson('/api/v1/supplier-contacts/'.$contact->id, [
            'name' => 'Updated Name',
            'is_primary' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.is_primary', true);

        $this->assertDatabaseHas('supplier_contacts', [
            'id' => $contact->id,
            'name' => 'Updated Name',
            'is_primary' => true,
        ]);
    }

    public function test_can_delete_supplier_contact(): void
    {
        Sanctum::actingAs($this->user);

        $contact = SupplierContact::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'To Delete',
            'email' => 'delete@example.com',
            'phone' => '08555',
            'title' => 'Officer',
        ]);

        $response = $this->deleteJson('/api/v1/supplier-contacts/'.$contact->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted('supplier_contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_validates_nonexistent_supplier_id_on_create_contact(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/supplier-contacts', [
            'supplier_id' => 99999,
            'name' => 'John Nonexistent',
            'email' => 'john@example.com',
            'phone' => '08123',
            'title' => 'Staff',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_id']);
    }

    public function test_update_contact_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/v1/supplier-contacts/99999', [
            'name' => 'Non Existent',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_contact_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/supplier-contacts/99999');

        $response->assertStatus(404);
    }
}
