<?php

namespace Tests\Feature\Purchasing;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\Core\File;
use App\Models\Purchasing\Supplier;
use App\Models\Purchasing\SupplierDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupplierDocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected Supplier $supplier;

    protected File $file;

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

        $this->file = File::create([
            'original_name' => 'siup.pdf',
            'stored_name' => 'siup_unique_123.pdf',
            'file_path' => 'documents/suppliers/siup_unique_123.pdf',
            'file_type' => 'application/pdf',
            'file_size' => '1024',
            'file_extension' => 'pdf',
            'file_mime_type' => 'application/pdf',
            'uploaded_by' => $this->user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_documents(): void
    {
        $response = $this->getJson('/api/v1/supplier-documents');
        $response->assertStatus(401);
    }

    public function test_can_list_supplier_documents(): void
    {
        Sanctum::actingAs($this->user);

        SupplierDocument::create([
            'supplier_id' => $this->supplier->id,
            'file_id' => $this->file->id,
            'document_number' => 'DOC-001',
            'document_type' => 'SIUP',
            'is_active' => true,
            'is_verified' => false,
        ]);

        $response = $this->getJson('/api/v1/supplier-documents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'supplier_id', 'file_id', 'document_number', 'document_type', 'is_verified'],
                ],
                'meta',
            ]);
    }

    public function test_can_filter_documents_by_supplier_and_type(): void
    {
        Sanctum::actingAs($this->user);

        SupplierDocument::create([
            'supplier_id' => $this->supplier->id,
            'file_id' => $this->file->id,
            'document_number' => 'DOC-SIUP-01',
            'document_type' => 'SIUP',
            'is_active' => true,
            'is_verified' => false,
        ]);

        SupplierDocument::create([
            'supplier_id' => $this->supplier->id,
            'file_id' => $this->file->id,
            'document_number' => 'DOC-NPWP-01',
            'document_type' => 'NPWP',
            'is_active' => true,
            'is_verified' => true,
        ]);

        $siupResponse = $this->getJson('/api/v1/supplier-documents?document_type=SIUP');
        $siupResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.document_type', 'SIUP');

        $verifiedResponse = $this->getJson('/api/v1/supplier-documents?is_verified=1');
        $verifiedResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.document_type', 'NPWP');
    }

    public function test_can_create_supplier_document(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'supplier_id' => $this->supplier->id,
            'file_id' => $this->file->id,
            'document_number' => 'DOC-NIB-2026',
            'document_type' => 'NIB',
            'issue_date' => '2026-01-01',
            'expiry_date' => '2030-01-01',
            'notes' => 'Valid registered NIB',
        ];

        $response = $this->postJson('/api/v1/supplier-documents', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.document_number', 'DOC-NIB-2026')
            ->assertJsonPath('data.document_type', 'NIB');

        $this->assertDatabaseHas('supplier_documents', [
            'document_number' => 'DOC-NIB-2026',
            'supplier_id' => $this->supplier->id,
        ]);
    }

    public function test_validation_fails_when_document_fields_missing(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/supplier-documents', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier_id', 'file_id', 'document_number', 'document_type']);
    }

    public function test_can_show_supplier_document(): void
    {
        Sanctum::actingAs($this->user);

        $document = SupplierDocument::create([
            'supplier_id' => $this->supplier->id,
            'file_id' => $this->file->id,
            'document_number' => 'DOC-SHOW-01',
            'document_type' => 'TDP',
        ]);

        $response = $this->getJson('/api/v1/supplier-documents/'.$document->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $document->id)
            ->assertJsonPath('data.document_number', 'DOC-SHOW-01');

        $notFoundResponse = $this->getJson('/api/v1/supplier-documents/99999');
        $notFoundResponse->assertStatus(404);
    }

    public function test_can_update_supplier_document(): void
    {
        Sanctum::actingAs($this->user);

        $document = SupplierDocument::create([
            'supplier_id' => $this->supplier->id,
            'file_id' => $this->file->id,
            'document_number' => 'DOC-OLD-01',
            'document_type' => 'OTHER',
        ]);

        $response = $this->patchJson('/api/v1/supplier-documents/'.$document->id, [
            'document_number' => 'DOC-UPDATED-01',
            'notes' => 'Updated document notes',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.document_number', 'DOC-UPDATED-01')
            ->assertJsonPath('data.notes', 'Updated document notes');

        $this->assertDatabaseHas('supplier_documents', [
            'id' => $document->id,
            'document_number' => 'DOC-UPDATED-01',
        ]);
    }

    public function test_can_verify_supplier_document(): void
    {
        Sanctum::actingAs($this->user);

        $document = SupplierDocument::create([
            'supplier_id' => $this->supplier->id,
            'file_id' => $this->file->id,
            'document_number' => 'DOC-VERIFY-01',
            'document_type' => 'SIUP',
            'is_verified' => false,
        ]);

        $response = $this->postJson('/api/v1/supplier-documents/'.$document->id.'/verify', [
            'notes' => 'Verified by compliance team',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_verified', true)
            ->assertJsonPath('data.verified_by', $this->user->id);

        $this->assertDatabaseHas('supplier_documents', [
            'id' => $document->id,
            'is_verified' => true,
            'verified_by' => $this->user->id,
        ]);
    }

    public function test_can_delete_supplier_document(): void
    {
        Sanctum::actingAs($this->user);

        $document = SupplierDocument::create([
            'supplier_id' => $this->supplier->id,
            'file_id' => $this->file->id,
            'document_number' => 'DOC-DEL-01',
            'document_type' => 'SIUP',
        ]);

        $response = $this->deleteJson('/api/v1/supplier-documents/'.$document->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted('supplier_documents', [
            'id' => $document->id,
        ]);
    }

    public function test_validates_nonexistent_file_id_on_create_document(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/supplier-documents', [
            'supplier_id' => $this->supplier->id,
            'file_id' => 99999,
            'document_number' => 'DOC-INVALID-FILE',
            'document_type' => 'SIUP',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_id']);
    }

    public function test_update_document_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/v1/supplier-documents/99999', [
            'document_number' => 'DOC-NONEXISTENT',
        ]);

        $response->assertStatus(404);
    }

    public function test_verify_document_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/supplier-documents/99999/verify', [
            'notes' => 'Verified notes',
        ]);

        $response->assertStatus(404);
    }

    public function test_delete_document_returns_404_when_not_found(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson('/api/v1/supplier-documents/99999');

        $response->assertStatus(404);
    }
}
