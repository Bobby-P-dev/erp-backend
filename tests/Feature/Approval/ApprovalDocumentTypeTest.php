<?php

declare(strict_types=1);

namespace Tests\Feature\Approval;

use App\Enums\Approval\ApprovalDocumentType;
use App\Enums\Approval\ApprovalMode;
use App\Enums\Approval\ApprovalRequestStatus;
use App\Enums\Approval\ApproverScope;
use App\Models\Approval\ApprovalConfiguration;
use App\Models\Approval\ApprovalConfigurationLevel;
use App\Models\Approval\ApprovalRequest;
use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\Core\JobLevel;
use App\Models\Core\Position;
use App\Models\Purchasing\PurchaseRequisition;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalDocumentTypeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_enum_metadata_and_options_are_correct(): void
    {
        $prType = ApprovalDocumentType::PurchaseRequisition;

        $this->assertSame('purchase_requisition', $prType->value);
        $this->assertSame('Purchasing', $prType->module());
        $this->assertSame(PurchaseRequisition::class, $prType->modelClass());
        $this->assertTrue($prType->hasAmount());
        $this->assertStringContainsString('Purchase Requisition', $prType->label());

        $option = $prType->toOption();
        $this->assertArrayHasKey('value', $option);
        $this->assertArrayHasKey('label', $option);
        $this->assertArrayHasKey('module', $option);
        $this->assertArrayHasKey('model_class', $option);
        $this->assertArrayHasKey('has_amount', $option);

        // Morph map
        $morphMap = ApprovalDocumentType::morphMap();
        $this->assertArrayHasKey('purchase_requisition', $morphMap);
        $this->assertSame(PurchaseRequisition::class, $morphMap['purchase_requisition']);

        // tryFromModelOrValue
        $this->assertSame($prType, ApprovalDocumentType::tryFromModelOrValue('purchase_requisition'));
        $this->assertSame($prType, ApprovalDocumentType::tryFromModelOrValue(PurchaseRequisition::class));
        $this->assertNull(ApprovalDocumentType::tryFromModelOrValue('non_existent_type'));
    }

    public function test_authenticated_user_can_get_grouped_document_types(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/approval-configurations/document-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'module',
                        'items' => [
                            '*' => [
                                'value',
                                'label',
                                'module',
                                'model_class',
                                'has_amount',
                            ],
                        ],
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $modules = array_column($data, 'module');
        $this->assertContains('Purchasing', $modules);
    }

    public function test_authenticated_user_can_get_flat_document_types(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/approval-configurations/document-types?grouped=false');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'value',
                        'label',
                        'module',
                        'model_class',
                        'has_amount',
                    ],
                ],
            ]);

        $values = array_column($response->json('data'), 'value');
        $this->assertContains('purchase_requisition', $values);
        $this->assertContains('purchase_order', $values);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/approval-configurations/document-types');

        $response->assertStatus(401);
    }

    public function test_approval_workflow_works_with_morph_slug_document_type(): void
    {
        $company = Company::create(['name' => 'PT Test Company', 'code' => 'TEST', 'is_active' => true]);
        $division = Division::create(['company_id' => $company->id, 'name' => 'General Affairs', 'code' => 'GA', 'is_active' => true]);
        $position = Position::create(['company_id' => $company->id, 'name' => 'Staff GA', 'code' => 'STF-GA', 'is_active' => true]);
        $jobLevel = JobLevel::create(['name' => 'Staff', 'code' => 'STF', 'is_active' => true]);
        $roleApprover = Role::create(['name' => 'Approver Test', 'guard_name' => 'web']);

        $employee = Employee::create([
            'company_id' => $company->id,
            'division_id' => $division->id,
            'position_id' => $position->id,
            'job_level_id' => $jobLevel->id,
            'nik' => 'EMP-TEST-001',
            'name' => 'Test Employee',
            'is_active' => true,
            'is_department_head' => false,
        ]);
        $this->user->update(['employee_id' => $employee->id]);

        $approverEmployee = Employee::create([
            'company_id' => $company->id,
            'division_id' => $division->id,
            'position_id' => $position->id,
            'job_level_id' => $jobLevel->id,
            'nik' => 'EMP-APP-001',
            'name' => 'Approver Person',
            'is_active' => true,
            'is_department_head' => true,
        ]);
        $approverUser = User::factory()->create(['employee_id' => $approverEmployee->id]);
        $approverUser->assignRole($roleApprover);

        // Simpan konfigurasi menggunakan morph slug: 'purchase_requisition'
        $config = ApprovalConfiguration::create([
            'company_id' => $company->id,
            'code' => 'WFL-TEST-SLUG',
            'name' => 'Workflow With Morph Slug',
            'document_type' => ApprovalDocumentType::PurchaseRequisition->value,
            'is_active' => true,
        ]);

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $config->id,
            'step_order' => 1,
            'step_name' => 'Dept Head Approval',
            'approver_scope' => ApproverScope::DepartmentHead,
            'approval_mode' => ApprovalMode::Any,
            'role_id' => $roleApprover->id,
            'can_be_skipped' => false,
            'required_approvers_count' => 1,
        ]);

        $pr = PurchaseRequisition::create([
            'company_id' => $company->id,
            'division_id' => $division->id,
            'requester_id' => $this->user->id,
            'pr_number' => 'PR-2026-TEST-SLUG',
            'request_date' => now(),
            'purpose' => 'Pengadaan Alat Kantor',
            'status' => 'draft',
        ]);

        $service = app(ApprovalService::class);
        $request = $service->submitDocument($pr, $this->user);

        $this->assertInstanceOf(ApprovalRequest::class, $request);
        $this->assertSame(ApprovalRequestStatus::Pending, $request->status);
        $this->assertSame('PR-2026-TEST-SLUG', $request->document_number);
    }
}
