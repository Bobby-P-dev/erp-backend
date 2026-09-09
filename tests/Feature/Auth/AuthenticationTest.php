<?php

namespace Tests\Feature\Auth;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\Purchasing\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Division $division;

    protected Employee $employee;

    protected User $employeeUser;

    protected Supplier $supplier;

    protected User $supplierUser;

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

        $this->employeeUser = User::create([
            'employee_id' => $this->employee->id,
            'account_type' => 'employee',
            'password' => bcrypt('password123'),
        ]);

        $this->supplier = Supplier::create([
            'company_id' => $this->company->id,
            'supplier_code' => 'SUP-001',
            'name' => 'PT Mitra Sejahtera',
            'email' => 'vendor@mitra.com',
            'is_active' => true,
        ]);

        $this->supplierUser = User::create([
            'supplier_id' => $this->supplier->id,
            'account_type' => 'supplier',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_employee_can_authenticate_using_nik(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'nik' => 'EMP001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('user.id', $this->employeeUser->id)
            ->assertJsonPath('user.account_type', 'employee')
            ->assertJsonPath('user.employee.nik', 'EMP001')
            ->assertJsonStructure(['token', 'user']);

        $this->assertAuthenticatedAs($this->employeeUser);
    }

    public function test_supplier_can_authenticate_using_email(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'vendor@mitra.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('user.id', $this->supplierUser->id)
            ->assertJsonPath('user.account_type', 'supplier')
            ->assertJsonPath('user.supplier.email', 'vendor@mitra.com')
            ->assertJsonStructure(['token', 'user']);

        $this->assertAuthenticatedAs($this->supplierUser);
    }

    public function test_employee_can_authenticate_using_generic_login_field(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'login' => 'EMP001',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.id', $this->employeeUser->id);

        $this->assertAuthenticatedAs($this->employeeUser);
    }

    public function test_supplier_can_authenticate_using_generic_login_field(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'login' => 'vendor@mitra.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.id', $this->supplierUser->id);

        $this->assertAuthenticatedAs($this->supplierUser);
    }

    public function test_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'nik' => 'EMP001',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);

        $this->assertGuest();
    }

    public function test_inactive_employee_cannot_authenticate(): void
    {
        $this->employee->update(['is_active' => false]);

        $response = $this->postJson('/api/v1/login', [
            'nik' => 'EMP001',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);

        $this->assertGuest();
    }

    public function test_inactive_supplier_cannot_authenticate(): void
    {
        $this->supplier->update(['is_active' => false]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'vendor@mitra.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $response = $this->actingAs($this->employeeUser, 'web')->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout successful']);

        $this->assertGuest('web');
    }
}
