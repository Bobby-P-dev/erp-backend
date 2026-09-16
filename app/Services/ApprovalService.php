<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Approval\Approvable;
use App\Enums\Approval\ApprovalActionType;
use App\Enums\Approval\ApprovalDocumentType;
use App\Enums\Approval\ApprovalLevelStatus;
use App\Enums\Approval\ApprovalMode;
use App\Enums\Approval\ApprovalRequestStatus;
use App\Enums\Approval\ApproverScope;
use App\Events\Approval\ApprovalRequestApproved;
use App\Events\Approval\ApprovalRequestRejected;
use App\Events\Approval\ApprovalRequestRevisionRequested;
use App\Events\Approval\ApprovalRequestSubmitted;
use App\Events\Approval\ApprovalStepApproved;
use App\Exceptions\Approval\ApprovalConfigurationNotFoundException;
use App\Exceptions\Approval\InvalidApprovalStateException;
use App\Exceptions\Approval\NoEligibleApproverException;
use App\Exceptions\Approval\UnauthorizedApproverException;
use App\Models\Approval\ApprovalAction;
use App\Models\Approval\ApprovalConfiguration;
use App\Models\Approval\ApprovalConfigurationLevel;
use App\Models\Approval\ApprovalRequest;
use App\Models\Approval\ApprovalRequestLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class ApprovalService
{
    /**
     * Submit a document for approval.
     * Mendukung dokumen finansial (dengan nominal) dan non-finansial (Universal Flow, misal: Cuti, PR, Stock Opname).
     *
     * @param  array<string, mixed>  $context
     *
     * @throws InvalidApprovalStateException
     * @throws ApprovalConfigurationNotFoundException
     * @throws NoEligibleApproverException
     */
    public function submitDocument(
        Model $approvable,
        User $requester,
        float|string|null $amount = null,
        array $context = []
    ): ApprovalRequest {
        $requester->loadMissing('employee');

        // Resolve amount and context from Approvable contract if available
        if ($amount === null && $approvable instanceof Approvable) {
            $amount = $approvable->getApprovalAmount();
        }
        $numericAmount = $amount !== null ? (float) $amount : null;

        if ($approvable instanceof Approvable) {
            $context = array_merge($approvable->getApprovalContext(), $context);
        }

        return DB::transaction(function () use ($approvable, $requester, $numericAmount, $context): ApprovalRequest {
            /** @var Model $lockedApprovable */
            $lockedApprovable = $approvable::query()
                ->whereKey($approvable->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            // Pengecekan active request di dalam proteksi lock approvable
            /** @var ApprovalRequest|null $activeRequest */
            $activeRequest = ApprovalRequest::query()
                ->where('approvable_type', $lockedApprovable->getMorphClass())
                ->where('approvable_id', $lockedApprovable->getKey())
                ->whereIn('status', [ApprovalRequestStatus::Pending->value, ApprovalRequestStatus::Revision->value])
                ->lockForUpdate()
                ->first();

            if ($activeRequest !== null) {
                if ($activeRequest->status === ApprovalRequestStatus::Revision) {
                    return $this->executeResubmit(
                        $activeRequest,
                        $lockedApprovable,
                        $requester,
                        $numericAmount,
                        'Resubmitted via submitDocument'
                    );
                }

                throw new InvalidApprovalStateException(
                    "Document [{$lockedApprovable->getMorphClass()} #{$lockedApprovable->getKey()}] already has an active approval request."
                );
            }

            $companyId = $approvable instanceof Approvable
                ? $approvable->getApprovalCompanyId()
                : ($lockedApprovable->getAttribute('company_id') ?? $requester->employee?->company_id);

            $configuration = $this->resolveConfiguration(
                $lockedApprovable->getMorphClass(),
                $companyId,
                $numericAmount
            );

            if (! $configuration || $configuration->levels->isEmpty()) {
                $amountInfo = $numericAmount !== null ? "amount [{$numericAmount}]" : 'non-financial criteria';
                throw new ApprovalConfigurationNotFoundException(
                    "No approval configuration found for document [{$lockedApprovable->getMorphClass()}] with {$amountInfo}."
                );
            }

            $targetDivisionId = $approvable instanceof Approvable
                ? $approvable->getApprovalDivisionId()
                : ($lockedApprovable->getAttribute('division_id') ?? $requester->employee?->division_id);

            $documentNumber = $approvable instanceof Approvable
                ? $approvable->getApprovalDocumentNumber()
                : ($lockedApprovable->getAttribute('document_number') ?? $lockedApprovable->getAttribute('pr_number') ?? $lockedApprovable->getAttribute('po_number') ?? (string) $lockedApprovable->getKey());

            $documentTitle = $approvable instanceof Approvable
                ? $approvable->getApprovalDocumentTitle()
                : ($lockedApprovable->getAttribute('purpose') ?? $lockedApprovable->getAttribute('title') ?? $lockedApprovable->getAttribute('description') ?? null);

            try {
                /** @var ApprovalRequest $approvalRequest */
                $approvalRequest = ApprovalRequest::create([
                    'approval_configuration_id' => $configuration->id,
                    'approvable_type' => $lockedApprovable->getMorphClass(),
                    'approvable_id' => $lockedApprovable->getKey(),
                    'document_number' => $documentNumber,
                    'document_title' => $documentTitle,
                    'requester_id' => $requester->id,
                    'current_step_order' => 1,
                    'status' => ApprovalRequestStatus::Pending,
                    'total_amount' => $numericAmount,
                    'submitted_at' => now(),
                    'metadata' => [
                        'document_number' => $documentNumber,
                        'document_title' => $documentTitle,
                        'configuration_code' => $configuration->code,
                        'configuration_name' => $configuration->name,
                        'requester_employee_id' => $requester->employee?->id,
                        'division_id' => $targetDivisionId,
                        'context' => $context,
                    ],
                ]);

                /** @var ApprovalConfigurationLevel $configLevel */
                foreach ($configuration->levels as $configLevel) {
                    $isConditionMet = $this->evaluateLevelCondition(
                        $configLevel->condition_type,
                        $configLevel->condition_value,
                        $numericAmount,
                        $context
                    );

                    $assignDivisionId = in_array($configLevel->approver_scope, [
                        ApproverScope::RoleAndDivision,
                        ApproverScope::JobLevelAndDivision,
                        ApproverScope::DepartmentHead,
                    ], true) ? $targetDivisionId : null;

                    if (! $isConditionMet) {
                        // Level otomatis di-skip jika kondisi dinamis tidak terpenuhi (misal: threshold nominal)
                        ApprovalRequestLevel::create([
                            'approval_request_id' => $approvalRequest->id,
                            'step_order' => $configLevel->step_order,
                            'step_name' => $configLevel->step_name,
                            'approver_scope' => $configLevel->approver_scope,
                            'approval_mode' => $configLevel->approval_mode,
                            'can_be_skipped' => true,
                            'required_approvers_count' => 0,
                            'role_id' => $configLevel->role_id,
                            'job_level_id' => $configLevel->job_level_id,
                            'specific_user_id' => $configLevel->specific_user_id,
                            'division_id' => $assignDivisionId,
                            'status' => ApprovalLevelStatus::Skipped,
                            'completed_at' => now(),
                        ]);

                        continue;
                    }

                    $requiredApprovers = $this->calculateRequiredApproversCount(
                        $configLevel->approval_mode,
                        $configLevel->approver_scope,
                        $assignDivisionId,
                        $configLevel->role_id,
                        $configLevel->job_level_id,
                        $configLevel->specific_user_id,
                        (bool) $configLevel->can_be_skipped
                    );

                    $initialStatus = ($configLevel->can_be_skipped && $requiredApprovers === 0)
                        ? ApprovalLevelStatus::Skipped
                        : ApprovalLevelStatus::Pending;

                    ApprovalRequestLevel::create([
                        'approval_request_id' => $approvalRequest->id,
                        'step_order' => $configLevel->step_order,
                        'step_name' => $configLevel->step_name,
                        'approver_scope' => $configLevel->approver_scope,
                        'approval_mode' => $configLevel->approval_mode,
                        'can_be_skipped' => (bool) $configLevel->can_be_skipped,
                        'required_approvers_count' => $requiredApprovers,
                        'role_id' => $configLevel->role_id,
                        'job_level_id' => $configLevel->job_level_id,
                        'specific_user_id' => $configLevel->specific_user_id,
                        'division_id' => $assignDivisionId,
                        'status' => $initialStatus,
                        'completed_at' => $initialStatus === ApprovalLevelStatus::Skipped ? now() : null,
                    ]);
                }
            } catch (QueryException $e) {
                if ($this->isUniqueConstraintViolation($e, 'uq_appr_req_active_morph')) {
                    throw new InvalidApprovalStateException(
                        'Concurrency collision: active approval request already exists for this document.'
                    );
                }
                throw $e;
            }

            // Tentukan step aktif pertama yang berstatus Pending
            /** @var ApprovalRequestLevel|null $firstActiveLevel */
            $firstActiveLevel = $approvalRequest->levels()
                ->where('status', ApprovalLevelStatus::Pending->value)
                ->orderBy('step_order', 'asc')
                ->first();

            if ($firstActiveLevel !== null) {
                $approvalRequest->update(['current_step_order' => $firstActiveLevel->step_order]);
                $this->notifyApprovableStatus($lockedApprovable, ApprovalRequestStatus::Pending, $approvalRequest);
                ApprovalRequestSubmitted::dispatch($approvalRequest, $lockedApprovable);
            } else {
                // Semua level otomatis skipped (Auto-approval)
                $approvalRequest->update([
                    'status' => ApprovalRequestStatus::Approved,
                    'completed_at' => now(),
                ]);
                $this->notifyApprovableStatus($lockedApprovable, ApprovalRequestStatus::Approved, $approvalRequest);
                ApprovalRequestApproved::dispatch($approvalRequest, $lockedApprovable);
            }

            return $approvalRequest->load(['levels', 'configuration']);
        });
    }

    /**
     * Resubmit a revised document for approval.
     *
     * @throws InvalidApprovalStateException
     * @throws UnauthorizedApproverException
     */
    public function resubmitDocument(
        Model $approvable,
        User $requester,
        float|string|null $amount = null,
        ?string $notes = null
    ): ApprovalRequest {
        $requester->loadMissing('employee');

        return DB::transaction(function () use ($approvable, $requester, $amount, $notes): ApprovalRequest {
            /** @var Model $lockedApprovable */
            $lockedApprovable = $approvable::query()
                ->whereKey($approvable->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            /** @var ApprovalRequest|null $approvalRequest */
            $approvalRequest = ApprovalRequest::query()
                ->where('approvable_type', $lockedApprovable->getMorphClass())
                ->where('approvable_id', $lockedApprovable->getKey())
                ->where('status', ApprovalRequestStatus::Revision->value)
                ->lockForUpdate()
                ->first();

            if (! $approvalRequest) {
                throw new InvalidApprovalStateException(
                    "No pending revision request found for document [{$lockedApprovable->getMorphClass()} #{$lockedApprovable->getKey()}]."
                );
            }

            if ((int) $approvalRequest->requester_id !== (int) $requester->id) {
                throw new UnauthorizedApproverException(
                    "Only the original requester [User ID: {$approvalRequest->requester_id}] can resubmit this document."
                );
            }

            $numericAmount = $amount !== null
                ? (float) $amount
                : ($approvalRequest->total_amount !== null ? (float) $approvalRequest->total_amount : null);

            return $this->executeResubmit($approvalRequest, $lockedApprovable, $requester, $numericAmount, $notes);
        });
    }

    /**
     * Process an approval decision using ordered pessimistic row locks.
     *
     * @throws InvalidApprovalStateException
     * @throws UnauthorizedApproverException
     */
    public function processDecision(
        ApprovalRequest $approvalRequest,
        User $user,
        ApprovalActionType|string $decision,
        ?string $notes = null,
        ?int $expectedStepOrder = null,
        bool $allowSelfApproval = false
    ): ApprovalRequest {
        $actionEnum = is_string($decision) ? ApprovalActionType::from($decision) : $decision;

        return DB::transaction(function () use ($approvalRequest, $user, $actionEnum, $notes, $expectedStepOrder, $allowSelfApproval): ApprovalRequest {
            /** @var Model $approvable */
            $approvable = $approvalRequest->approvable()
                ->lockForUpdate()
                ->firstOrFail();

            /** @var ApprovalRequest $lockedRequest */
            $lockedRequest = ApprovalRequest::query()
                ->whereKey($approvalRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->status !== ApprovalRequestStatus::Pending) {
                throw new InvalidApprovalStateException(
                    "Approval request [ID: {$lockedRequest->id}] is not pending (Current status: {$lockedRequest->status->value})."
                );
            }

            if ($expectedStepOrder !== null && $lockedRequest->current_step_order !== $expectedStepOrder) {
                throw new InvalidApprovalStateException(
                    'Step mismatch: Level has already been transitioned by another approver.'
                );
            }

            /** @var ApprovalRequestLevel $currentLevel */
            $currentLevel = ApprovalRequestLevel::query()
                ->where('approval_request_id', $lockedRequest->id)
                ->where('step_order', $lockedRequest->current_step_order)
                ->lockForUpdate()
                ->firstOrFail();

            if ($currentLevel->status !== ApprovalLevelStatus::Pending) {
                throw new InvalidApprovalStateException(
                    "Approval level [Step: {$currentLevel->step_order}] is not in pending state."
                );
            }

            if (! $this->isUserAuthorizedForLevel($user, $currentLevel, $allowSelfApproval)) {
                throw new UnauthorizedApproverException(
                    "User [ID: {$user->id}] is not authorized to act on step [{$currentLevel->step_order} - {$currentLevel->step_name}]."
                );
            }

            try {
                $approvalAction = ApprovalAction::create([
                    'approval_request_id' => $lockedRequest->id,
                    'approval_request_level_id' => $currentLevel->id,
                    'user_id' => $user->id,
                    'action' => $actionEnum,
                    'notes' => $notes,
                    'acted_at' => now(),
                    'ip_address' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                ]);
            } catch (QueryException $e) {
                if ($this->isUniqueConstraintViolation($e, 'uq_appr_act_lvl_user')) {
                    throw new InvalidApprovalStateException(
                        "User [ID: {$user->id}] has already submitted an action for step [{$currentLevel->step_order}]."
                    );
                }
                throw $e;
            }

            match ($actionEnum) {
                ApprovalActionType::Reject => $this->handleRejection($lockedRequest, $currentLevel, $approvable, $notes),
                ApprovalActionType::RequestRevision => $this->handleRevision($lockedRequest, $currentLevel, $approvable, $notes),
                ApprovalActionType::Approve => $this->handleApproval($lockedRequest, $currentLevel, $approvable, $approvalAction),
                ApprovalActionType::Resubmit => throw new InvalidApprovalStateException('Resubmit action must be processed via resubmitDocument().'),
            };

            return $lockedRequest->fresh(['levels', 'actions']);
        });
    }

    /**
     * Strict User-to-Step Authorization Checker dengan proteksi Maker-Checker.
     */
    public function isUserAuthorizedForLevel(
        User $user,
        ApprovalRequestLevel $requestLevel,
        bool $allowSelfApproval = false
    ): bool {
        $requestLevel->loadMissing('request');

        if (! $allowSelfApproval && $requestLevel->request && (int) $user->id === (int) $requestLevel->request->requester_id) {
            return false;
        }

        $user->loadMissing(['employee', 'roles']);
        $employee = $user->employee;

        return match ($requestLevel->approver_scope) {
            ApproverScope::SpecificUser => (int) $user->id === (int) $requestLevel->specific_user_id,

            ApproverScope::RoleOnly => $requestLevel->role_id !== null
                && $user->roles->contains('id', $requestLevel->role_id),

            ApproverScope::RoleAndDivision => $employee !== null
                && $requestLevel->role_id !== null
                && $requestLevel->division_id !== null
                && (int) $employee->division_id === (int) $requestLevel->division_id
                && $user->roles->contains('id', $requestLevel->role_id),

            ApproverScope::JobLevelAndDivision => $employee !== null
                && $requestLevel->job_level_id !== null
                && $requestLevel->division_id !== null
                && (int) $employee->division_id === (int) $requestLevel->division_id
                && (int) $employee->job_level_id === (int) $requestLevel->job_level_id,

            ApproverScope::DepartmentHead => $employee !== null
                && $requestLevel->division_id !== null
                && (bool) ($employee->is_department_head ?? false)
                && (int) $employee->division_id === (int) $requestLevel->division_id,
        };
    }

    /**
     * Mengambil seluruh pending approval requests untuk user.
     *
     * @return Collection<int, ApprovalRequest>
     */
    public function getPendingRequestsForUser(User $user, bool $allowSelfApproval = false): Collection
    {
        $user->loadMissing(['employee', 'roles']);
        $employee = $user->employee;
        $roleIds = $user->roles->pluck('id')->all();

        return ApprovalRequest::query()
            ->where('status', ApprovalRequestStatus::Pending->value)
            ->when(! $allowSelfApproval, function (Builder $query) use ($user): void {
                $query->where('requester_id', '!=', $user->id);
            })
            ->whereHas('currentLevel', function (Builder $query) use ($user, $employee, $roleIds): void {
                $query->where('status', ApprovalLevelStatus::Pending->value)
                    ->whereDoesntHave('actions', function (Builder $actionQuery) use ($user): void {
                        $actionQuery->where('user_id', $user->id);
                    })
                    ->where(function (Builder $sub) use ($user, $employee, $roleIds): void {
                        $sub->where(function (Builder $q) use ($user): void {
                            $q->where('approver_scope', ApproverScope::SpecificUser->value)
                                ->where('specific_user_id', $user->id);
                        });

                        if (! empty($roleIds)) {
                            $sub->orWhere(function (Builder $q) use ($roleIds): void {
                                $q->where('approver_scope', ApproverScope::RoleOnly->value)
                                    ->whereIn('role_id', $roleIds);
                            });
                        }

                        if ($employee && $employee->division_id) {
                            if (! empty($roleIds)) {
                                $sub->orWhere(function (Builder $q) use ($employee, $roleIds): void {
                                    $q->where('approver_scope', ApproverScope::RoleAndDivision->value)
                                        ->where('division_id', $employee->division_id)
                                        ->whereIn('role_id', $roleIds);
                                });
                            }

                            if ($employee->job_level_id) {
                                $sub->orWhere(function (Builder $q) use ($employee): void {
                                    $q->where('approver_scope', ApproverScope::JobLevelAndDivision->value)
                                        ->where('division_id', $employee->division_id)
                                        ->where('job_level_id', $employee->job_level_id);
                                });
                            }

                            if ((bool) ($employee->is_department_head ?? false)) {
                                $sub->orWhere(function (Builder $q) use ($employee): void {
                                    $q->where('approver_scope', ApproverScope::DepartmentHead->value)
                                        ->where('division_id', $employee->division_id);
                                });
                            }
                        }
                    });
            })
            ->with(['currentLevel', 'approvable', 'requester'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Resolusi konfigurasi: mendukung universal non-finansial dan range finansial bertingkat.
     */
    private function resolveConfiguration(
        string $documentType,
        ?int $companyId,
        ?float $numericAmount
    ): ?ApprovalConfiguration {
        $aliases = [$documentType];
        $enumDocType = ApprovalDocumentType::tryFromModelOrValue($documentType);
        if ($enumDocType !== null) {
            $aliases[] = $enumDocType->value;
            $aliases[] = $enumDocType->modelClass();
        }

        $query = ApprovalConfiguration::query()
            ->whereIn('document_type', array_values(array_unique($aliases)))
            ->where('is_active', true)
            ->when($companyId !== null, function (Builder $q) use ($companyId): void {
                $q->where(function (Builder $sub) use ($companyId): void {
                    $sub->where('company_id', $companyId)
                        ->orWhereNull('company_id');
                });
            });

        if ($numericAmount !== null) {
            $query->where(function (Builder $q) use ($numericAmount): void {
                // Universal matching
                $q->where(function (Builder $sub): void {
                    $sub->whereNull('min_amount')->whereNull('max_amount');
                })
                // Financial range matching
                    ->orWhere(function (Builder $sub) use ($numericAmount): void {
                        $sub->where(function (Builder $sub2) use ($numericAmount): void {
                            $sub2->whereNull('min_amount')
                                ->orWhere('min_amount', '<=', $numericAmount);
                        })->where(function (Builder $sub2) use ($numericAmount): void {
                            $sub2->whereNull('max_amount')
                                ->orWhere('max_amount', '>=', $numericAmount);
                        });
                    });
            })
                ->orderByRaw('company_id IS NOT NULL DESC')
                ->orderByRaw('min_amount IS NOT NULL DESC')
                ->orderByDesc('min_amount');
        } else {
            // Non-financial: prioritaskan konfigurasi murni universal
            $query->where(function (Builder $q): void {
                $q->whereNull('min_amount')
                    ->orWhere('min_amount', 0.00);
            })
                ->orderByRaw('company_id IS NOT NULL DESC')
                ->orderByRaw('min_amount IS NULL DESC');
        }

        return $query->with(['levels' => fn ($q) => $q->orderBy('step_order', 'asc')])->first();
    }

    /**
     * Evaluasi kondisi dinamis pada tingkat level.
     *
     * @param  array<string, mixed>  $context
     */
    private function evaluateLevelCondition(
        ?string $conditionType,
        ?string $conditionValue,
        ?float $numericAmount,
        array $context = []
    ): bool {
        if ($conditionType === null || $conditionType === '' || in_array($conditionType, ['always', 'none'], true)) {
            return true;
        }

        return match ($conditionType) {
            'amount_gte', 'min_amount' => $numericAmount !== null && $conditionValue !== null && $numericAmount >= (float) $conditionValue,
            'amount_lte', 'max_amount' => $numericAmount !== null && $conditionValue !== null && $numericAmount <= (float) $conditionValue,
            'context_equals' => $this->evaluateContextEquals($conditionValue, $context),
            default => true,
        };
    }

    /**
     * Evaluasi kondisi berbasis payload context, format: 'key:value'.
     *
     * @param  array<string, mixed>  $context
     */
    private function evaluateContextEquals(?string $conditionValue, array $context): bool
    {
        if ($conditionValue === null || ! str_contains($conditionValue, ':')) {
            return true;
        }

        [$key, $expectedVal] = explode(':', $conditionValue, 2);
        $actualVal = $context[$key] ?? null;

        if (is_bool($actualVal)) {
            $expectedVal = filter_var($expectedVal, FILTER_VALIDATE_BOOLEAN);
        }

        return $actualVal === $expectedVal;
    }

    /**
     * Eksekusi resubmit: reset level-level transaksi dan audit trail.
     */
    private function executeResubmit(
        ApprovalRequest $approvalRequest,
        Model $lockedApprovable,
        User $requester,
        ?float $numericAmount,
        ?string $notes = null
    ): ApprovalRequest {
        $approvalRequest->loadMissing('levels');

        foreach ($approvalRequest->levels as $level) {
            $newStatus = ($level->can_be_skipped && $level->required_approvers_count === 0)
                ? ApprovalLevelStatus::Skipped
                : ApprovalLevelStatus::Pending;

            $level->update([
                'status' => $newStatus,
                'completed_at' => null,
            ]);
        }

        /** @var ApprovalRequestLevel|null $firstActiveLevel */
        $firstActiveLevel = $approvalRequest->levels()
            ->where('status', ApprovalLevelStatus::Pending->value)
            ->orderBy('step_order', 'asc')
            ->first();

        $firstStepOrder = $firstActiveLevel ? $firstActiveLevel->step_order : 1;

        $documentNumber = $lockedApprovable instanceof Approvable
            ? $lockedApprovable->getApprovalDocumentNumber()
            : ($lockedApprovable->getAttribute('document_number') ?? $lockedApprovable->getAttribute('pr_number') ?? $lockedApprovable->getAttribute('po_number') ?? (string) $lockedApprovable->getKey());

        $documentTitle = $lockedApprovable instanceof Approvable
            ? $lockedApprovable->getApprovalDocumentTitle()
            : ($lockedApprovable->getAttribute('purpose') ?? $lockedApprovable->getAttribute('title') ?? $lockedApprovable->getAttribute('description') ?? null);

        $approvalRequest->update([
            'document_number' => $documentNumber,
            'document_title' => $documentTitle,
            'status' => ApprovalRequestStatus::Pending,
            'current_step_order' => $firstStepOrder,
            'total_amount' => $numericAmount,
            'submitted_at' => now(),
        ]);

        ApprovalAction::create([
            'approval_request_id' => $approvalRequest->id,
            'approval_request_level_id' => $firstActiveLevel?->id ?? $approvalRequest->levels->first()?->id,
            'user_id' => $requester->id,
            'action' => ApprovalActionType::Resubmit,
            'notes' => $notes ?? 'Document resubmitted after revision.',
            'acted_at' => now(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        $this->notifyApprovableStatus($lockedApprovable, ApprovalRequestStatus::Pending, $approvalRequest);
        ApprovalRequestSubmitted::dispatch($approvalRequest, $lockedApprovable);

        return $approvalRequest->fresh(['levels', 'configuration', 'actions']);
    }

    /**
     * Handle document rejection.
     */
    private function handleRejection(
        ApprovalRequest $request,
        ApprovalRequestLevel $currentLevel,
        Model $approvable,
        ?string $reason = null
    ): void {
        $currentLevel->update([
            'status' => ApprovalLevelStatus::Rejected,
            'completed_at' => now(),
        ]);

        ApprovalRequestLevel::query()
            ->where('approval_request_id', $request->id)
            ->where('step_order', '>', $currentLevel->step_order)
            ->where('status', ApprovalLevelStatus::Pending->value)
            ->update(['status' => ApprovalLevelStatus::Skipped->value]);

        $request->update([
            'status' => ApprovalRequestStatus::Rejected,
            'completed_at' => now(),
        ]);

        $this->notifyApprovableStatus($approvable, ApprovalRequestStatus::Rejected, $request);
        ApprovalRequestRejected::dispatch($request, $approvable, $reason);
    }

    /**
     * Handle revision request.
     */
    private function handleRevision(
        ApprovalRequest $request,
        ApprovalRequestLevel $currentLevel,
        Model $approvable,
        ?string $reason = null
    ): void {
        $currentLevel->update([
            'status' => ApprovalLevelStatus::Revision,
        ]);

        $request->update([
            'status' => ApprovalRequestStatus::Revision,
        ]);

        $this->notifyApprovableStatus($approvable, ApprovalRequestStatus::Revision, $request);
        ApprovalRequestRevisionRequested::dispatch($request, $approvable, $reason);
    }

    /**
     * Handle approval logic respecting 'any' vs 'all' modes with locked evaluation.
     */
    private function handleApproval(
        ApprovalRequest $request,
        ApprovalRequestLevel $currentLevel,
        Model $approvable,
        ApprovalAction $action
    ): void {
        ApprovalStepApproved::dispatch($request, $currentLevel, $action, $approvable);

        $levelCompleted = false;

        if ($currentLevel->approval_mode === ApprovalMode::Any) {
            $levelCompleted = true;
        } elseif ($currentLevel->approval_mode === ApprovalMode::All) {
            $approvedActionsCount = ApprovalAction::query()
                ->where('approval_request_level_id', $currentLevel->id)
                ->where('action', ApprovalActionType::Approve->value)
                ->count();

            if ($approvedActionsCount >= $currentLevel->required_approvers_count) {
                $levelCompleted = true;
            }
        }

        if (! $levelCompleted) {
            return;
        }

        $currentLevel->update([
            'status' => ApprovalLevelStatus::Approved,
            'completed_at' => now(),
        ]);

        /** @var ApprovalRequestLevel|null $nextLevel */
        $nextLevel = ApprovalRequestLevel::query()
            ->where('approval_request_id', $request->id)
            ->where('step_order', '>', $currentLevel->step_order)
            ->where('status', ApprovalLevelStatus::Pending->value)
            ->orderBy('step_order', 'asc')
            ->first();

        if ($nextLevel !== null) {
            $request->update([
                'current_step_order' => $nextLevel->step_order,
            ]);
        } else {
            $request->update([
                'status' => ApprovalRequestStatus::Approved,
                'completed_at' => now(),
            ]);

            $this->notifyApprovableStatus($approvable, ApprovalRequestStatus::Approved, $request);
            ApprovalRequestApproved::dispatch($request, $approvable);
        }
    }

    /**
     * Lifecycle notifier to approvable model (Contract or fallback).
     */
    private function notifyApprovableStatus(Model $approvable, ApprovalRequestStatus $status, ?ApprovalRequest $request = null): void
    {
        if ($approvable instanceof Approvable) {
            $approvable->onApprovalStatusChanged($status, $request);
        } elseif (array_key_exists('status', $approvable->getAttributes()) || $approvable->isFillable('status')) {
            $statusValue = match ($status) {
                ApprovalRequestStatus::Pending => 'pending_approval',
                ApprovalRequestStatus::Approved => 'approved',
                ApprovalRequestStatus::Rejected => 'rejected',
                ApprovalRequestStatus::Revision => 'revision_requested',
                ApprovalRequestStatus::Cancelled => 'cancelled',
            };
            $approvable->update(['status' => $statusValue]);
        }
    }

    /**
     * Calculate and snapshot the number of required approvers at submission time.
     *
     * @throws NoEligibleApproverException
     */
    private function calculateRequiredApproversCount(
        ApprovalMode $mode,
        ApproverScope $scope,
        ?int $divisionId,
        ?int $roleId,
        ?int $jobLevelId,
        ?int $specificUserId,
        bool $canBeSkipped = false
    ): int {
        $count = $this->countEligibleApprovers($scope, $divisionId, $roleId, $jobLevelId, $specificUserId);

        if ($mode === ApprovalMode::Any) {
            if ($count === 0) {
                if ($canBeSkipped) {
                    return 0;
                }
                throw new NoEligibleApproverException(
                    "Cannot initialize approval step: no active approvers found in database for scope [{$scope->value}]."
                );
            }

            return 1;
        }

        if ($count === 0) {
            if ($canBeSkipped) {
                return 0;
            }
            throw new NoEligibleApproverException(
                "Cannot initialize approval step: no active approvers found in database for scope [{$scope->value}]."
            );
        }

        return $count;
    }

    /**
     * Count eligible approvers matching the scope configuration.
     */
    private function countEligibleApprovers(
        ApproverScope $scope,
        ?int $divisionId,
        ?int $roleId,
        ?int $jobLevelId,
        ?int $specificUserId
    ): int {
        return match ($scope) {
            ApproverScope::SpecificUser => $specificUserId !== null ? User::query()->whereKey($specificUserId)->count() : 0,

            ApproverScope::DepartmentHead => $divisionId !== null
                ? User::query()
                    ->whereHas('employee', function (Builder $query) use ($divisionId): void {
                        $query->where('division_id', $divisionId)
                            ->where('is_department_head', true);
                    })
                    ->count()
                : 0,

            ApproverScope::JobLevelAndDivision => ($divisionId !== null && $jobLevelId !== null)
                ? User::query()
                    ->whereHas('employee', function (Builder $query) use ($divisionId, $jobLevelId): void {
                        $query->where('division_id', $divisionId)
                            ->where('job_level_id', $jobLevelId);
                    })
                    ->count()
                : 0,

            ApproverScope::RoleOnly => $roleId !== null
                ? User::query()
                    ->whereHas('roles', function (Builder $query) use ($roleId): void {
                        $query->where('roles.id', $roleId);
                    })
                    ->count()
                : 0,

            ApproverScope::RoleAndDivision => ($roleId !== null && $divisionId !== null)
                ? User::query()
                    ->whereHas('roles', function (Builder $query) use ($roleId): void {
                        $query->where('roles.id', $roleId);
                    })
                    ->whereHas('employee', function (Builder $query) use ($divisionId): void {
                        $query->where('division_id', $divisionId);
                    })
                    ->count()
                : 0,
        };
    }

    /**
     * Database-agnostic detection for unique constraint violations (MySQL, PostgreSQL, SQLite).
     */
    private function isUniqueConstraintViolation(QueryException $e, string $constraintName): bool
    {
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $driverCode = (int) ($e->errorInfo[1] ?? 0);
        $message = $e->getMessage();

        $isUnique = in_array($sqlState, ['23000', '23505'], true)
            || in_array($driverCode, [1062, 19, 2067], true);

        return $isUnique && str_contains($message, $constraintName);
    }
}
