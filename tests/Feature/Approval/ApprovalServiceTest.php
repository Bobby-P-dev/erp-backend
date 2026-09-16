<?php

declare(strict_types=1);

namespace Tests\Feature\Approval;

use App\Enums\Approval\ApprovalActionType;
use App\Enums\Approval\ApprovalLevelStatus;
use App\Enums\Approval\ApprovalMode;
use App\Enums\Approval\ApprovalRequestStatus;
use App\Enums\Approval\ApproverScope;
use App\Events\Approval\ApprovalRequestApproved;
use App\Events\Approval\ApprovalRequestSubmitted;
use App\Events\Approval\ApprovalStepApproved;
use App\Exceptions\Approval\InvalidApprovalStateException;
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
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalService $service;

    protected Company $company;

    protected Division $division;

    protected Position $position;

    protected JobLevel $staffJobLevel;

    protected JobLevel $managerJobLevel;

    protected Role $financeRole;

    protected User $requester;

    protected User $deptHead;

    protected User $financeApprover;

    protected PurchaseRequisition $document;

    protected ApprovalConfiguration $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ApprovalService::class);

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

        $this->position = Position::create([
            'company_id' => $this->company->id,
            'code' => 'PUR-OFF',
            'name' => 'Purchasing Officer',
            'is_active' => true,
        ]);

        $this->staffJobLevel = JobLevel::create([
            'code' => 'STF',
            'name' => 'Staff',
            'is_active' => true,
        ]);

        $this->managerJobLevel = JobLevel::create([
            'code' => 'MGR',
            'name' => 'Manager',
            'is_active' => true,
        ]);

        $this->financeRole = Role::create([
            'name' => 'finance-approver',
            'guard_name' => 'web',
        ]);

        // 1. Requester User & Employee
        $requesterEmployee = Employee::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'position_id' => $this->position->id,
            'job_level_id' => $this->staffJobLevel->id,
            'nik' => 'EMP001',
            'name' => 'Requester Staff',
            'is_active' => true,
            'is_department_head' => false,
        ]);

        $this->requester = User::create([
            'name' => 'Requester Staff',
            'username' => 'requester_staff',
            'email' => 'requester@padma.co.id',
            'password' => bcrypt('password'),
            'employee_id' => $requesterEmployee->id,
        ]);

        // 2. Department Head User & Employee
        $deptHeadEmployee = Employee::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'position_id' => $this->position->id,
            'job_level_id' => $this->managerJobLevel->id,
            'nik' => 'EMP002',
            'name' => 'Dept Head Purchasing',
            'is_active' => true,
            'is_department_head' => true,
        ]);

        $this->deptHead = User::create([
            'name' => 'Dept Head Purchasing',
            'username' => 'dept_head',
            'email' => 'head.purchasing@padma.co.id',
            'password' => bcrypt('password'),
            'employee_id' => $deptHeadEmployee->id,
        ]);

        // 3. Finance Approver User & Employee
        $financeEmployee = Employee::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'position_id' => $this->position->id,
            'job_level_id' => $this->managerJobLevel->id,
            'nik' => 'EMP003',
            'name' => 'Finance Manager',
            'is_active' => true,
            'is_department_head' => false,
        ]);

        $this->financeApprover = User::create([
            'name' => 'Finance Manager',
            'username' => 'finance_mgr',
            'email' => 'finance@padma.co.id',
            'password' => bcrypt('password'),
            'employee_id' => $financeEmployee->id,
        ]);
        $this->financeApprover->assignRole($this->financeRole);

        // 4. Sample Document (PurchaseRequisition)
        $this->document = PurchaseRequisition::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->requester->id,
            'pr_number' => 'PR-2026-0001',
            'request_date' => now()->toDateString(),
            'status' => 'draft',
            'purpose' => 'Pengadaan spare parts mesin',
        ]);

        // 5. Approval Configuration (Tier 1: Dept Head, Tier 2: Finance Role)
        $this->configuration = ApprovalConfiguration::create([
            'company_id' => $this->company->id,
            'document_type' => PurchaseRequisition::class,
            'code' => 'APPR-PR-01',
            'name' => 'Alur Persetujuan PR Standard',
            'min_amount' => 0.00,
            'max_amount' => null,
            'is_active' => true,
        ]);

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $this->configuration->id,
            'step_order' => 1,
            'step_name' => 'Persetujuan Ka. Divisi',
            'approver_scope' => ApproverScope::DepartmentHead,
            'approval_mode' => ApprovalMode::Any,
            'can_be_skipped' => false,
        ]);

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $this->configuration->id,
            'step_order' => 2,
            'step_name' => 'Persetujuan Keuangan / Finance',
            'approver_scope' => ApproverScope::RoleOnly,
            'role_id' => $this->financeRole->id,
            'approval_mode' => ApprovalMode::Any,
            'can_be_skipped' => false,
        ]);
    }

    public function test_submit_document_creates_request_and_snapshot_levels(): void
    {
        $request = $this->service->submitDocument($this->document, $this->requester, 5000000);

        $this->assertInstanceOf(ApprovalRequest::class, $request);
        $this->assertEquals(ApprovalRequestStatus::Pending, $request->status);
        $this->assertEquals(1, $request->current_step_order);
        $this->assertEquals('PR-2026-0001', $request->document_number);
        $this->assertEquals('Pengadaan spare parts mesin', $request->document_title);
        $this->assertEquals(5000000, (float) $request->total_amount);
        $this->assertCount(2, $request->levels);

        $this->document->refresh();
        $this->assertEquals('pending_approval', $this->document->status);

        // Verifikasi eager loading currentLevel bekerja tanpa SQL 1054 error
        $loadedRequest = ApprovalRequest::with('currentLevel')->find($request->id);
        $this->assertNotNull($loadedRequest?->currentLevel);
        $this->assertEquals(1, $loadedRequest->currentLevel->step_order);
        $this->assertEquals('Persetujuan Ka. Divisi', $loadedRequest->currentLevel->step_name);
    }

    public function test_cannot_submit_duplicate_active_request(): void
    {
        $this->service->submitDocument($this->document, $this->requester, 1000000);

        $this->expectException(InvalidApprovalStateException::class);
        $this->service->submitDocument($this->document, $this->requester, 1000000);
    }

    public function test_is_user_authorized_for_level_and_maker_checker_protection(): void
    {
        $request = $this->service->submitDocument($this->document, $this->requester, 2500000);
        $level1 = $request->currentLevel;

        // Dept Head pemohon berhak menyetujui level 1
        $this->assertTrue($this->service->isUserAuthorizedForLevel($this->deptHead, $level1));

        // Finance Approver belum berhak di level 1 (scope Dept Head)
        $this->assertFalse($this->service->isUserAuthorizedForLevel($this->financeApprover, $level1));

        // Maker-Checker: Pemohon (requester) dilarang meng-approve dokumen sendiri
        $this->assertFalse($this->service->isUserAuthorizedForLevel($this->requester, $level1));
    }

    public function test_process_decision_approve_moves_to_next_level_and_completes(): void
    {
        $request = $this->service->submitDocument($this->document, $this->requester, 1500000);

        // Step 1: Dept Head menyetujui
        $request = $this->service->processDecision(
            $request,
            $this->deptHead,
            ApprovalActionType::Approve,
            'Disetujui Ka. Divisi Purchasing'
        );

        $this->assertEquals(ApprovalRequestStatus::Pending, $request->status);
        $this->assertEquals(2, $request->current_step_order);

        // Step 2: Finance Manager menyetujui
        $request = $this->service->processDecision(
            $request,
            $this->financeApprover,
            ApprovalActionType::Approve,
            'Disetujui Finance'
        );

        $this->assertEquals(ApprovalRequestStatus::Approved, $request->status);
        $this->assertNotNull($request->completed_at);

        $this->document->refresh();
        $this->assertEquals('approved', $this->document->status);
    }

    public function test_process_decision_reject_stops_and_marks_subsequent_levels_skipped(): void
    {
        $request = $this->service->submitDocument($this->document, $this->requester, 1500000);

        // Step 1: Dept Head menolak
        $request = $this->service->processDecision(
            $request,
            $this->deptHead,
            ApprovalActionType::Reject,
            'Ditolak: Anggaran divisi overlimit'
        );

        $this->assertEquals(ApprovalRequestStatus::Rejected, $request->status);
        $this->assertNotNull($request->completed_at);

        $levels = $request->levels()->orderBy('step_order')->get();
        $this->assertEquals(ApprovalLevelStatus::Rejected, $levels[0]->status);
        $this->assertEquals(ApprovalLevelStatus::Skipped, $levels[1]->status);

        $this->document->refresh();
        $this->assertEquals('rejected', $this->document->status);
    }

    public function test_process_decision_revision_and_resubmit_workflow(): void
    {
        $request = $this->service->submitDocument($this->document, $this->requester, 1500000);

        // Step 1: Dept Head meminta revisi
        $request = $this->service->processDecision(
            $request,
            $this->deptHead,
            ApprovalActionType::RequestRevision,
            'Mohon lampirkan quotation vendor pembanding'
        );

        $this->assertEquals(ApprovalRequestStatus::Revision, $request->status);
        $this->assertTrue($request->canBeResubmitted());

        $this->document->refresh();
        $this->assertEquals('revision_requested', $this->document->status);

        // Requester melakukan revisi dan resubmit
        $resubmittedRequest = $this->service->resubmitDocument(
            $this->document,
            $this->requester,
            1400000,
            'Sudah dilampirkan 3 quotation'
        );

        $this->assertEquals(ApprovalRequestStatus::Pending, $resubmittedRequest->status);
        $this->assertEquals(1, $resubmittedRequest->current_step_order);
        $this->assertEquals(1400000, (float) $resubmittedRequest->total_amount);

        // Seluruh level telah di-reset ke Pending
        foreach ($resubmittedRequest->levels as $level) {
            $this->assertEquals(ApprovalLevelStatus::Pending, $level->status);
        }

        // Audit action Resubmit tercatat
        $latestAction = $resubmittedRequest->actions()->latest('id')->first();
        $this->assertEquals(ApprovalActionType::Resubmit, $latestAction?->action);

        $this->document->refresh();
        $this->assertEquals('pending_approval', $this->document->status);
    }

    public function test_get_pending_requests_for_user_filters_properly(): void
    {
        $this->service->submitDocument($this->document, $this->requester, 2000000);

        // Dept Head melihat 1 dokumen pending di inbox-nya
        $deptHeadInbox = $this->service->getPendingRequestsForUser($this->deptHead);
        $this->assertCount(1, $deptHeadInbox);

        // Finance Approver belum melihat dokumen (masih di step 1)
        $financeInbox = $this->service->getPendingRequestsForUser($this->financeApprover);
        $this->assertCount(0, $financeInbox);

        // Requester tidak melihat dokumen buatannya sendiri di inbox persetujuan (Maker-Checker)
        $requesterInbox = $this->service->getPendingRequestsForUser($this->requester);
        $this->assertCount(0, $requesterInbox);
    }

    public function test_polymorphic_integrity_barrier_prevents_deleting_approvable_with_active_request(): void
    {
        $this->service->submitDocument($this->document, $this->requester, 1000000);

        $this->expectException(InvalidApprovalStateException::class);
        $this->expectExceptionMessage('Integrity Violation: Cannot delete document');

        // Mencoba hard-delete atau delete saat approval aktif
        $this->document->delete();
    }

    public function test_can_be_skipped_level_initializes_as_skipped_when_no_approver_exists(): void
    {
        // Buat divisi baru tanpa karyawan apapun
        $emptyDivision = Division::create([
            'company_id' => $this->company->id,
            'code' => 'MKT',
            'name' => 'Marketing',
            'is_active' => true,
        ]);

        $emptyDoc = PurchaseRequisition::create([
            'company_id' => $this->company->id,
            'division_id' => $emptyDivision->id,
            'requester_id' => $this->requester->id,
            'pr_number' => 'PR-2026-0002',
            'request_date' => now()->toDateString(),
            'status' => 'draft',
            'purpose' => 'Peralatan marketing',
        ]);

        // Buat config khusus di mana Step 1 adalah Dept Head yang can_be_skipped = true
        $cfg = ApprovalConfiguration::create([
            'company_id' => $this->company->id,
            'document_type' => PurchaseRequisition::class,
            'code' => 'APPR-OPTIONAL-01',
            'name' => 'Alur Optional Dept Head',
            'min_amount' => 10000000.00,
            'max_amount' => null,
            'is_active' => true,
        ]);

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $cfg->id,
            'step_order' => 1,
            'step_name' => 'Optional Ka. Divisi',
            'approver_scope' => ApproverScope::DepartmentHead,
            'approval_mode' => ApprovalMode::Any,
            'can_be_skipped' => true,
        ]);

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $cfg->id,
            'step_order' => 2,
            'step_name' => 'Finance Approval',
            'approver_scope' => ApproverScope::RoleOnly,
            'role_id' => $this->financeRole->id,
            'approval_mode' => ApprovalMode::Any,
            'can_be_skipped' => false,
        ]);

        $req = $this->service->submitDocument($emptyDoc, $this->requester, 15000000);

        $levels = $req->levels()->orderBy('step_order')->get();
        // Step 1 otomatis Skipped karena 0 approver & can_be_skipped = true
        $this->assertEquals(ApprovalLevelStatus::Skipped, $levels[0]->status);
        // Step 2 tetap Pending
        $this->assertEquals(ApprovalLevelStatus::Pending, $levels[1]->status);
    }

    public function test_submit_non_financial_document_matches_universal_configuration(): void
    {
        // Buat Universal Configuration tanpa min/max amount (null)
        $universalCfg = ApprovalConfiguration::create([
            'company_id' => $this->company->id,
            'document_type' => PurchaseRequisition::class,
            'code' => 'APPR-UNIVERSAL-PR',
            'name' => 'Alur Universal PR Non-Finansial',
            'min_amount' => null,
            'max_amount' => null,
            'is_active' => true,
        ]);

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $universalCfg->id,
            'step_order' => 1,
            'step_name' => 'Ka. Divisi Approval',
            'approver_scope' => ApproverScope::DepartmentHead,
            'approval_mode' => ApprovalMode::Any,
        ]);

        $nonFinancialDoc = PurchaseRequisition::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->requester->id,
            'pr_number' => 'PR-NON-FINANCIAL-01',
            'request_date' => now()->toDateString(),
            'status' => 'draft',
            'purpose' => 'Permintaan sample bahan baku uji lab',
        ]);

        // Submit tanpa parameter amount (null)
        $req = $this->service->submitDocument($nonFinancialDoc, $this->requester, null);

        $this->assertNull($req->total_amount);
        $this->assertEquals($universalCfg->id, $req->approval_configuration_id);
        $this->assertEquals(ApprovalRequestStatus::Pending, $req->status);
        $this->assertEquals('pending_approval', $nonFinancialDoc->fresh()->status);
    }

    public function test_dynamic_level_condition_skips_level_when_threshold_not_reached(): void
    {
        $this->configuration->update(['is_active' => false]);

        $dynamicCfg = ApprovalConfiguration::create([
            'company_id' => $this->company->id,
            'document_type' => PurchaseRequisition::class,
            'code' => 'APPR-DYNAMIC-LEVELS',
            'name' => 'Alur Dinamis Level Berjenjang',
            'min_amount' => null,
            'max_amount' => null,
            'is_active' => true,
        ]);

        // Level 1: Selalu aktif
        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $dynamicCfg->id,
            'step_order' => 1,
            'step_name' => 'Ka. Divisi (Wajib)',
            'approver_scope' => ApproverScope::DepartmentHead,
            'approval_mode' => ApprovalMode::Any,
            'condition_type' => 'always',
        ]);

        // Level 2: Hanya aktif jika nominal >= 50.000.000
        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $dynamicCfg->id,
            'step_order' => 2,
            'step_name' => 'Finance Director (Jika >= 50jt)',
            'approver_scope' => ApproverScope::RoleOnly,
            'role_id' => $this->financeRole->id,
            'approval_mode' => ApprovalMode::Any,
            'condition_type' => 'amount_gte',
            'condition_value' => '50000000',
        ]);

        $smallDoc = PurchaseRequisition::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'requester_id' => $this->requester->id,
            'pr_number' => 'PR-SMALL-01',
            'request_date' => now()->toDateString(),
            'status' => 'draft',
            'purpose' => 'Pembelian kecil di bawah 50jt',
        ]);

        // Submit dokumen dengan nominal 10jt (< 50jt threshold)
        $req = $this->service->submitDocument($smallDoc, $this->requester, 10000000);

        $levels = $req->levels()->orderBy('step_order')->get();
        $this->assertEquals(ApprovalLevelStatus::Pending, $levels[0]->status);
        // Level 2 otomatis Skipped karena kondisi amount_gte tidak terpenuhi
        $this->assertEquals(ApprovalLevelStatus::Skipped, $levels[1]->status);

        // Ketika Ka. Divisi approve Level 1, dokumen langsung tuntas (Approved) karena Level 2 sudah skipped
        $req = $this->service->processDecision($req, $this->deptHead, ApprovalActionType::Approve, 'Approved step 1');
        $this->assertEquals(ApprovalRequestStatus::Approved, $req->status);
        $this->assertEquals('approved', $smallDoc->fresh()->status);
    }

    public function test_domain_events_dispatched_during_approval_lifecycle(): void
    {
        Event::fake([
            ApprovalRequestSubmitted::class,
            ApprovalStepApproved::class,
            ApprovalRequestApproved::class,
        ]);

        $req = $this->service->submitDocument($this->document, $this->requester, 2000000);
        Event::assertDispatched(ApprovalRequestSubmitted::class);

        $req = $this->service->processDecision($req, $this->deptHead, ApprovalActionType::Approve, 'Step 1 ok');
        Event::assertDispatched(ApprovalStepApproved::class);

        $this->service->processDecision($req, $this->financeApprover, ApprovalActionType::Approve, 'Step 2 ok');
        Event::assertDispatched(ApprovalRequestApproved::class);
    }
}
