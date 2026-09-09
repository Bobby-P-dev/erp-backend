<?php

namespace Tests\Feature\Auth;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\Purchasing\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Division $division;

    protected Employee $employee;

    protected Supplier $supplier;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'code' => 'PSI',
            'name' => 'PT Padma Soode Indonesia',
            'is_active' => true,
        ]);

        $this->division = Division::create([
            'company_id' => $this->company->id,
            'code' => 'PUR',
            'name' => 'Purchasing',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'nik' => 'EMP001',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'company_id' => $this->company->id,
            'supplier_code' => 'SUP-001',
            'name' => 'PT Mitra Sejahtera',
            'email' => 'vendor@mitra.com',
            'is_active' => true,
        ]);

        // Admin user for sanctum authentication on register route
        $adminEmployee = Employee::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'nik' => 'ADMIN01',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'employee_id' => $adminEmployee->id,
            'account_type' => 'employee',
            'password' => bcrypt('password123'),
        ]);

        Sanctum::actingAs($this->adminUser);
    }

    public function test_can_register_user_for_employee(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'employee_id' => $this->employee->id,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'User account created successfully')
            ->assertJsonPath('data.employee_id', $this->employee->id)
            ->assertJsonPath('data.account_type', 'employee');

        $this->assertDatabaseHas('users', [
            'employee_id' => $this->employee->id,
            'account_type' => 'employee',
        ]);
    }

    public function test_can_register_user_for_supplier(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'supplier_id' => $this->supplier->id,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'User account created successfully')
            ->assertJsonPath('data.supplier_id', $this->supplier->id)
            ->assertJsonPath('data.account_type', 'supplier');

        $this->assertDatabaseHas('users', [
            'supplier_id' => $this->supplier->id,
            'account_type' => 'supplier',
        ]);
    }

    public function test_cannot_register_with_both_employee_and_supplier_id(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'employee_id' => $this->employee->id,
            'supplier_id' => $this->supplier->id,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['employee_id', 'supplier_id']);
    }
}
