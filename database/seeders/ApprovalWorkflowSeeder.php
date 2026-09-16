<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Approval\ApprovalActionType;
use App\Enums\Approval\ApprovalDocumentType;
use App\Enums\Approval\ApprovalMode;
use App\Enums\Approval\ApproverScope;
use App\Models\Approval\ApprovalConfiguration;
use App\Models\Approval\ApprovalConfigurationLevel;
use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\Core\Employee;
use App\Models\Purchasing\PurchaseRequisition;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ApprovalWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $approvalService = app(ApprovalService::class);

        // 1. Master Company & Divisi
        $company = Company::firstOrCreate(
            ['code' => 'PSI'],
            ['name' => 'PT Padma Soode Indonesia', 'is_active' => true]
        );

        $divPpic = Division::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'PPIC'],
            ['name' => 'Production Planning & Inventory Control', 'is_active' => true]
        );

        $divPurchasing = Division::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'PUR'],
            ['name' => 'Purchasing', 'is_active' => true]
        );

        $divFinance = Division::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'FIN'],
            ['name' => 'Finance & Accounting', 'is_active' => true]
        );

        $divManagement = Division::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'BOD'],
            ['name' => 'Board of Directors', 'is_active' => true]
        );

        // 2. Roles untuk Workflow Engine (Spatie)
        $roleManagerPpic = Role::firstOrCreate(['name' => 'Manager PPIC', 'guard_name' => 'web']);
        $roleManagerPurchasing = Role::firstOrCreate(['name' => 'Manager Purchasing', 'guard_name' => 'web']);
        $roleFinanceApprover = Role::firstOrCreate(['name' => 'Finance Approver', 'guard_name' => 'web']);
        $roleDirector = Role::firstOrCreate(['name' => 'Director', 'guard_name' => 'web']);

        $defaultPassword = Hash::make('password');

        // Helper untuk membuat Employee + User
        $createUserWithEmployee = function (
            string $nik,
            string $name,
            string $email,
            int $divisionId,
            bool $isDeptHead,
            ?Role $role = null
        ) use ($company, $defaultPassword): User {
            $employee = Employee::firstOrCreate(
                ['company_id' => $company->id, 'nik' => $nik],
                [
                    'division_id' => $divisionId,
                    'name' => $name,
                    'email' => $email,
                    'is_active' => true,
                    'is_department_head' => $isDeptHead,
                ]
            );

            // Update is_department_head jika sudah ada sebelumnya
            if ($employee->is_department_head !== $isDeptHead) {
                $employee->update(['is_department_head' => $isDeptHead]);
            }

            $user = User::firstOrCreate(
                ['employee_id' => $employee->id],
                ['password' => $defaultPassword]
            );

            if ($role !== null && ! $user->hasRole($role)) {
                $user->assignRole($role);
            }

            return $user;
        };

        // 3. User Aktor Skenario
        // A. Tim PPIC
        $userBudiStaffPpic = $createUserWithEmployee(
            'EMP-PPIC-001',
            'Budi Pratama (Staff PPIC)',
            'budi.ppic@padmasoode.co.id',
            $divPpic->id,
            false
        );

        $userHendraHeadPpic = $createUserWithEmployee(
            'EMP-PPIC-002',
            'Hendra Wijaya (Head of PPIC)',
            'hendra.ppic@padmasoode.co.id',
            $divPpic->id,
            true,
            $roleManagerPpic
        );

        // B. Tim Purchasing
        $userRinaStaffPurchasing = $createUserWithEmployee(
            'EMP-PUR-002',
            'Rina Kusuma (Staff Purchasing)',
            'rina.purchasing@padmasoode.co.id',
            $divPurchasing->id,
            false
        );

        $userYokoManagerPurchasing = $createUserWithEmployee(
            'EMP-PUR-003',
            'Yoko Santoso (Manager Purchasing)',
            'yoko.purchasing@padmasoode.co.id',
            $divPurchasing->id,
            true,
            $roleManagerPurchasing
        );

        // C. Tim Finance
        $userDewiFinance = $createUserWithEmployee(
            'EMP-FIN-001',
            'Dewi Sartika (Finance Manager)',
            'dewi.finance@padmasoode.co.id',
            $divFinance->id,
            true,
            $roleFinanceApprover
        );

        // D. Direksi
        $userBambangDirector = $createUserWithEmployee(
            'EMP-DIR-001',
            'Bambang Gunawan (Director)',
            'bambang.director@padmasoode.co.id',
            $divManagement->id,
            true,
            $roleDirector
        );

        // 4. Template Master Alur Approval (approval_configurations & levels)

        // Template A: Alur Universal Purchase Requisition (Non-Finansial / Universal Flow)
        /** @var ApprovalConfiguration $configPr */
        $configPr = ApprovalConfiguration::updateOrCreate(
            ['code' => 'WFL-PR-UNIVERSAL'],
            [
                'company_id' => $company->id,
                'document_type' => ApprovalDocumentType::PurchaseRequisition->value,
                'name' => 'Alur Persetujuan Pengadaan Barang (PR)',
                'min_amount' => null,
                'max_amount' => null,
                'is_active' => true,
                'description' => 'Alur persetujuan pengadaan material internal pabrik (Universal: PPIC/Divisi -> Ka. Divisi -> Purchasing)',
            ]
        );

        $configPr->levels()->delete();

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $configPr->id,
            'step_order' => 1,
            'step_name' => 'Persetujuan Kepala Divisi Pemohon',
            'approver_scope' => ApproverScope::DepartmentHead,
            'approval_mode' => ApprovalMode::Any,
            'can_be_skipped' => false,
        ]);

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $configPr->id,
            'step_order' => 2,
            'step_name' => 'Review Kebutuhan Material oleh PPIC',
            'approver_scope' => ApproverScope::RoleOnly,
            'role_id' => $roleManagerPpic->id,
            'approval_mode' => ApprovalMode::Any,
            'can_be_skipped' => false,
        ]);

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $configPr->id,
            'step_order' => 3,
            'step_name' => 'Otorisasi Final Purchasing Manager',
            'approver_scope' => ApproverScope::RoleOnly,
            'role_id' => $roleManagerPurchasing->id,
            'approval_mode' => ApprovalMode::Any,
            'can_be_skipped' => false,
        ]);

        // Template B: Alur Purchase Order Finansial (Tiered PO)
        /** @var ApprovalConfiguration $configPo */
        $configPo = ApprovalConfiguration::updateOrCreate(
            ['code' => 'WFL-PO-TIERED'],
            [
                'company_id' => $company->id,
                'document_type' => ApprovalDocumentType::PurchaseOrder->value,
                'name' => 'Alur Persetujuan Purchase Order Resmi (PO)',
                'min_amount' => null,
                'max_amount' => null,
                'is_active' => true,
                'description' => 'Pemesanan resmi ke vendor (Step 1 Purchasing, Step 2 Finance, Step 3 Direktur jika >= 100jt)',
            ]
        );

        $configPo->levels()->delete();

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $configPo->id,
            'step_order' => 1,
            'step_name' => 'Verifikasi Purchasing Manager',
            'approver_scope' => ApproverScope::RoleOnly,
            'role_id' => $roleManagerPurchasing->id,
            'approval_mode' => ApprovalMode::Any,
            'can_be_skipped' => false,
        ]);

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $configPo->id,
            'step_order' => 2,
            'step_name' => 'Persetujuan Keuangan / Finance Manager',
            'approver_scope' => ApproverScope::RoleOnly,
            'role_id' => $roleFinanceApprover->id,
            'approval_mode' => ApprovalMode::Any,
            'can_be_skipped' => false,
        ]);

        ApprovalConfigurationLevel::create([
            'approval_configuration_id' => $configPo->id,
            'step_order' => 3,
            'step_name' => 'Otorisasi Direktur (Khusus Nominal >= 100 Juta)',
            'approver_scope' => ApproverScope::RoleOnly,
            'role_id' => $roleDirector->id,
            'approval_mode' => ApprovalMode::Any,
            'condition_type' => 'amount_gte',
            'condition_value' => '100000000',
            'can_be_skipped' => true,
        ]);

        // 5. Sampel Dokumen Transaksi Nyata untuk Diaudit/Dibaca User

        // Skenario 1: PR Aktif Sedang Menunggu di Meja Ka. Divisi PPIC (Pending Step 1)
        $prPending = PurchaseRequisition::updateOrCreate(
            ['pr_number' => 'PR-2026-PPIC-0001'],
            [
                'company_id' => $company->id,
                'division_id' => $divPpic->id,
                'requester_id' => $userBudiStaffPpic->id,
                'request_date' => now()->toDateString(),
                'required_date' => now()->addDays(7)->toDateString(),
                'status' => 'draft',
                'purpose' => 'Permintaan Resin Plastik ABS 2.000 kg untuk Sales Order #SO-8821',
            ]
        );

        if (! $prPending->activeApprovalRequest()->exists()) {
            $approvalService->submitDocument($prPending, $userBudiStaffPpic, null, [
                'sales_order_ref' => 'SO-8821',
                'urgency' => 'normal',
            ]);
        }

        // Skenario 2: PR yang Diminta Revisi oleh Pimpinan (Status: revision_requested)
        $prRevision = PurchaseRequisition::updateOrCreate(
            ['pr_number' => 'PR-2026-PUR-0002'],
            [
                'company_id' => $company->id,
                'division_id' => $divPurchasing->id,
                'requester_id' => $userRinaStaffPurchasing->id,
                'request_date' => now()->subDay()->toDateString(),
                'required_date' => now()->addDays(5)->toDateString(),
                'status' => 'draft',
                'purpose' => 'Pengadaan Alat Ukur Precision Caliper 5 unit',
            ]
        );

        if (! $prRevision->approvalRequests()->exists()) {
            $reqRev = $approvalService->submitDocument($prRevision, $userRinaStaffPurchasing, null);
            $approvalService->processDecision(
                $reqRev,
                $userYokoManagerPurchasing,
                ApprovalActionType::RequestRevision,
                'Mohon lampirkan rincian spesifikasi toleransi kalibrasi alat sebelum disetujui.'
            );
        }

        // Skenario 3: PR yang Sudah Sah Disetujui Lengkap Seluruh Pihak (Status: approved)
        $prApproved = PurchaseRequisition::updateOrCreate(
            ['pr_number' => 'PR-2026-PPIC-0003'],
            [
                'company_id' => $company->id,
                'division_id' => $divPpic->id,
                'requester_id' => $userBudiStaffPpic->id,
                'request_date' => now()->subDays(3)->toDateString(),
                'required_date' => now()->addDays(3)->toDateString(),
                'status' => 'draft',
                'purpose' => 'Sparepart Emergency Bearing Mesin Injection Molding #3',
            ]
        );

        if (! $prApproved->approvalRequests()->exists()) {
            $reqApp = $approvalService->submitDocument($prApproved, $userBudiStaffPpic, null);

            // Step 1: Ka. Divisi PPIC approve
            $reqApp = $approvalService->processDecision(
                $reqApp,
                $userHendraHeadPpic,
                ApprovalActionType::Approve,
                'Kebutuhan valid untuk jadwal produksi minggu ini.'
            );

            // Step 2: Review Tim PPIC approve
            $reqApp = $approvalService->processDecision(
                $reqApp,
                $userHendraHeadPpic,
                ApprovalActionType::Approve,
                'Stok gudang sudah dipastikan kosong, silakan dibeli.'
            );

            // Step 3: Purchasing Manager approve
            $approvalService->processDecision(
                $reqApp,
                $userYokoManagerPurchasing,
                ApprovalActionType::Approve,
                'Disetujui. Tim Purchasing segera terbitkan PO ke supplier.'
            );
        }
    }
}
