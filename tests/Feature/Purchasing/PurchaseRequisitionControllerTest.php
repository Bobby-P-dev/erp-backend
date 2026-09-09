<?php

namespace Tests\Feature\Purchasing;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\DocumentNumberSequence;
use App\Models\Core\Employee;
use App\Models\Purchasing\Item;
use App\Models\Purchasing\PurchaseRequisition;
use App\Models\Purchasing\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PurchaseRequisitionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Division $division;

    protected Employee $employee;

    protected User $user;

    protected Unit $unit;

    protected Item $item;

    protected DocumentNumberSequence $sequence;

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

        $this->user = User::create([
            'employee_id' => $this->employee->id,
            'password' => bcrypt('password'),
        ]);

        $this->unit = Unit::create([
            'code' => 'PCS',
            'name' => 'Pieces',
        ]);

        $this->item = Item::create([
            'code' => 'ITM-001',
            'name' => 'Baut Hexagonal',
            'description' => 'Baut baja hexagonal M8',
            'item_type' => 'Raw Material',
            'unit_id' => $this->unit->id,
        ]);

        $this->sequence = DocumentNumberSequence::create([
            'company_id' => $this->company->id,
            'category' => 'PR',
            'period' => '2026',
            'prefix' => 'PR',
            'current_number' => 0,
            'format' => '{prefix}-{period}-{number}',
            'number_length' => 6,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_index_returns_paginated_purchase_requisitions(): void
    {
        $pr1 = PurchaseRequisition::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'pr_number' => 'PR-2026-000001',
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'status' => 'draft',
            'purpose' => 'Batch 1',
        ]);

        $pr2 = PurchaseRequisition::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'pr_number' => 'PR-2026-000002',
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-16',
            'status' => 'draft',
            'purpose' => 'Batch 2',
        ]);

        $response = $this->getJson('/api/v1/purchase-requisitions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'pr_number',
                        'company_id',
                        'division_id',
                        'requester_id',
                        'request_date',
                        'required_date',
                        'status',
                        'purpose',
                        'notes',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_store_creates_purchase_requisition_successfully(): void
    {
        $payload = [
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'purpose' => 'Kebutuhan mesin cetak',
            'notes' => 'Catatan penting pengadaan',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 50,
                    'notes' => 'Spesifikasi standar',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/purchase-requisitions', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.pr_number', 'PR-2026-000001')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.company.code', 'PSI')
            ->assertJsonPath('data.division.code', 'PUR')
            ->assertJsonPath('data.requester.id', $this->user->id)
            ->assertJsonPath('data.items.0.item.name', 'Baut Hexagonal')
            ->assertJsonPath('data.items.0.quantity', 50)
            ->assertJsonPath('message', 'Purchase Requisition created successfully');

        $this->assertDatabaseHas('purchase_requestions', [
            'pr_number' => 'PR-2026-000001',
            'purpose' => 'Kebutuhan mesin cetak',
        ]);

        $this->assertDatabaseHas('purchase_requestion_items', [
            'item_id' => $this->item->id,
            'quantity' => 50,
            'notes' => 'Spesifikasi standar',
        ]);
    }

    public function test_store_validates_request_and_rejects_invalid_data(): void
    {
        $response = $this->postJson('/api/v1/purchase-requisitions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'company_id',
                'division_id',
                'requester_id',
                'request_date',
                'required_date',
                'purpose',
                'items',
            ]);
    }

    public function test_show_returns_purchase_requisition_by_id(): void
    {
        $pr = PurchaseRequisition::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'pr_number' => 'PR-2026-000001',
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'status' => 'draft',
            'purpose' => 'Detail test',
        ]);

        $response = $this->getJson("/api/v1/purchase-requisitions/{$pr->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $pr->id)
            ->assertJsonPath('data.pr_number', 'PR-2026-000001')
            ->assertJsonPath('data.company.code', 'PSI')
            ->assertJsonPath('data.division.code', 'PUR');
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/v1/purchase-requisitions/999999');

        $response->assertStatus(404);
    }

    public function test_submit_changes_status_to_submitted(): void
    {
        $pr = PurchaseRequisition::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'pr_number' => 'PR-2026-000001',
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'status' => 'draft',
            'purpose' => 'Submit PR',
        ]);

        $response = $this->postJson("/api/v1/purchase-requisitions/{$pr->id}/submit");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('message', 'Purchase Requisition submitted successfully');

        $this->assertEquals('submitted', $pr->fresh()->status);
    }

    public function test_cannot_submit_already_submitted_purchase_requisition(): void
    {
        $pr = PurchaseRequisition::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->user->id,
            'pr_number' => 'PR-2026-000001',
            'request_date' => '2026-09-09',
            'required_date' => '2026-09-15',
            'status' => 'submitted', // Already submitted
            'purpose' => 'Submit twice',
        ]);

        $response = $this->postJson("/api/v1/purchase-requisitions/{$pr->id}/submit");

        $response->assertStatus(422)
            ->assertJsonPath('message', "Purchase Requisition with status 'submitted' cannot be submitted.");
    }
}
