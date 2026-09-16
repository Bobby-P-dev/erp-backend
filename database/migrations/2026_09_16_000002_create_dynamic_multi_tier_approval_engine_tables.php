<?php

declare(strict_types=1);

use App\Enums\Approval\ApprovalActionType;
use App\Enums\Approval\ApprovalLevelStatus;
use App\Enums\Approval\ApprovalMode;
use App\Enums\Approval\ApprovalRequestStatus;
use App\Enums\Approval\ApproverScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. approval_configurations
        Schema::create('approval_configurations', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            // RESTRICT: Menghapus company dilarang jika masih memiliki konfigurasi persetujuan
            $table->foreignId('company_id')->nullable()->constrained('companies')->restrictOnDelete();
            $table->string('document_type', 150);
            $table->string('code', 50)->unique('uq_appr_cfg_code');
            $table->string('name', 150);
            $table->decimal('min_amount', 15, 2)->default(0.00);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            // Total composite length: (150 * 4) + 1 + 8 + 7 = 616 bytes (Aman dari limit 3072 bytes InnoDB)
            $table->index(['document_type', 'is_active', 'company_id', 'min_amount'], 'idx_appr_cfg_lookup');
            $table->index(['min_amount', 'max_amount'], 'idx_appr_cfg_amounts');
        });

        // 2. approval_configuration_levels
        Schema::create('approval_configuration_levels', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            // CASCADE: Jika template konfigurasi dihapus (belum ada transaksi), hapus seluruh level template
            $table->foreignId('approval_configuration_id')->constrained('approval_configurations')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('step_name', 100);
            $table->enum('approver_scope', array_column(ApproverScope::cases(), 'value'));
            // RESTRICT: Role, JobLevel, User master tidak boleh di-hard delete jika masih dipakai template
            $table->foreignId('role_id')->nullable()->constrained('roles')->restrictOnDelete();
            $table->foreignId('job_level_id')->nullable()->constrained('job_levels')->restrictOnDelete();
            $table->foreignId('specific_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->enum('approval_mode', array_column(ApprovalMode::cases(), 'value'))->default(ApprovalMode::Any->value);
            $table->boolean('can_be_skipped')->default(false);
            $table->string('condition_type', 50)->nullable();
            $table->string('condition_value', 100)->nullable();
            $table->unsignedSmallInteger('sla_hours')->nullable();
            $table->timestamps();

            $table->unique(['approval_configuration_id', 'step_order'], 'uq_appr_cfg_lvl_step');
            $table->index(['role_id'], 'idx_appr_cfg_lvl_role');
            $table->index(['job_level_id'], 'idx_appr_cfg_lvl_job_lvl');
            $table->index(['specific_user_id'], 'idx_appr_cfg_lvl_user');
        });

        // 3. approval_requests
        Schema::create('approval_requests', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            // RESTRICT: Konfigurasi tidak boleh dihapus jika sudah digunakan oleh request transaksi aktif/historis
            $table->foreignId('approval_configuration_id')->constrained('approval_configurations')->restrictOnDelete();
            $table->string('approvable_type', 150);
            $table->unsignedBigInteger('approvable_id');
            // RESTRICT: User pembuat tidak boleh dihapus dari database jika memiliki riwayat request
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('current_step_order')->default(1);
            $table->enum('status', array_column(ApprovalRequestStatus::cases(), 'value'))->default(ApprovalRequestStatus::Pending->value);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();

            // MySQL InnoDB Remediation: Virtual Generated Column pengganti Partial Unique Index
            // Nilai NULL tidak melanggar keunikan. Status 'pending' dan 'revision' menghasilkan nilai 'ACTIVE'.
            $table->string('active_guard', 20)
                ->virtualAs("CASE WHEN status IN ('pending', 'revision') THEN 'ACTIVE' ELSE NULL END")
                ->nullable();

            $table->timestamps();

            // Total composite length: (150 * 4) + 8 + (20 * 4) = 688 bytes (Aman dari limit 3072 bytes)
            $table->unique(['approvable_type', 'approvable_id', 'active_guard'], 'uq_appr_req_active_morph');

            $table->index(['approvable_type', 'approvable_id'], 'idx_appr_req_morph');
            $table->index(['status', 'current_step_order'], 'idx_appr_req_status_step');
            $table->index(['requester_id', 'status'], 'idx_appr_req_requester_status');
        });

        // 4. approval_request_levels (Snapshot per transaction instance)
        Schema::create('approval_request_levels', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            // CASCADE: Jika approval request dihapus, hapus snapshot level terkait
            $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('step_name', 100);
            $table->enum('approver_scope', array_column(ApproverScope::cases(), 'value'));
            $table->enum('approval_mode', array_column(ApprovalMode::cases(), 'value'))->default(ApprovalMode::Any->value);
            $table->boolean('can_be_skipped')->default(false);
            $table->unsignedInteger('required_approvers_count')->default(1);
            // RESTRICT: Mencegah penghapusan master data jika terdapat snapshot approval aktif/historis
            $table->foreignId('role_id')->nullable()->constrained('roles')->restrictOnDelete();
            $table->foreignId('job_level_id')->nullable()->constrained('job_levels')->restrictOnDelete();
            $table->foreignId('specific_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->restrictOnDelete();
            $table->enum('status', array_column(ApprovalLevelStatus::cases(), 'value'))->default(ApprovalLevelStatus::Pending->value);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['approval_request_id', 'step_order'], 'uq_appr_req_lvl_step');

            // Optimized Composite Indexes for Pending Approver Queries (ENUM = 1 byte)
            $table->index(['status', 'approver_scope', 'specific_user_id'], 'idx_appr_lvl_pending_user');
            $table->index(['status', 'approver_scope', 'division_id', 'role_id'], 'idx_appr_lvl_pending_role_div');
            $table->index(['status', 'approver_scope', 'division_id', 'job_level_id'], 'idx_appr_lvl_pending_job_div');
            $table->index(['status', 'approver_scope', 'division_id'], 'idx_appr_lvl_pending_dept_head');
        });

        // 5. approval_actions (Audit Trail & Idempotency Barrier)
        Schema::create('approval_actions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();
            // CASCADE: Integritas internal agregat request
            $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->foreignId('approval_request_level_id')->constrained('approval_request_levels')->cascadeOnDelete();
            // RESTRICT: Aktor penandatangan tidak boleh dihapus dari database (Audit Log Compliance)
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('action', array_column(ApprovalActionType::cases(), 'value'));
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Mencegah duplicate action race condition pada step yang sama
            $table->unique(['approval_request_level_id', 'user_id'], 'uq_appr_act_lvl_user');

            $table->index(['approval_request_id', 'acted_at'], 'idx_appr_act_req_time');
            $table->index(['user_id', 'action'], 'idx_appr_act_user_act');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
        Schema::dropIfExists('approval_request_levels');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_configuration_levels');
        Schema::dropIfExists('approval_configurations');
    }
};
