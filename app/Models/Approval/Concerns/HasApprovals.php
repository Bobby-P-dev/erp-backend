<?php

declare(strict_types=1);

namespace App\Models\Approval\Concerns;

use App\Enums\Approval\ApprovalRequestStatus;
use App\Exceptions\Approval\InvalidApprovalStateException;
use App\Models\Approval\ApprovalRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasApprovals
{
    /**
     * Boot trait HasApprovals.
     * Mencegah pembersihan/penghapusan data fisik pada entitas approvable
     * apabila masih terdapat transaksi persetujuan yang aktif (Polymorphic Integrity Barrier).
     */
    public static function bootHasApprovals(): void
    {
        static::deleting(function (Model $model): void {
            $hasActiveApproval = $model->morphOne(ApprovalRequest::class, 'approvable')
                ->whereIn('status', [ApprovalRequestStatus::Pending->value, ApprovalRequestStatus::Revision->value])
                ->exists();

            if ($hasActiveApproval) {
                throw new InvalidApprovalStateException(
                    "Integrity Violation: Cannot delete document [{$model->getMorphClass()} #{$model->getKey()}] while an active approval request is pending."
                );
            }
        });
    }

    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable')->orderByDesc('id');
    }

    public function latestApprovalRequest(): MorphOne
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable')->latestOfMany();
    }

    public function activeApprovalRequest(): MorphOne
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable')
            ->whereIn('status', [ApprovalRequestStatus::Pending->value, ApprovalRequestStatus::Revision->value])
            ->latestOfMany();
    }

    /**
     * Default resolver for company ID.
     */
    public function getApprovalCompanyId(): ?int
    {
        return isset($this->company_id) ? (int) $this->company_id : null;
    }

    /**
     * Default resolver for division ID.
     */
    public function getApprovalDivisionId(): ?int
    {
        return isset($this->division_id) ? (int) $this->division_id : null;
    }

    /**
     * Default resolver for financial transaction amount.
     * Returns null if the document is non-financial (e.g. Leave, Material Request).
     */
    public function getApprovalAmount(): ?float
    {
        if (isset($this->total_amount)) {
            return (float) $this->total_amount;
        }

        if (isset($this->amount)) {
            return (float) $this->amount;
        }

        return null;
    }

    /**
     * Default resolver for document number (PR, PO, SO, etc.).
     */
    public function getApprovalDocumentNumber(): ?string
    {
        return $this->document_number
            ?? $this->pr_number
            ?? $this->po_number
            ?? $this->number
            ?? $this->code
            ?? (string) $this->getKey();
    }

    /**
     * Default resolver for document title, purpose, or description.
     */
    public function getApprovalDocumentTitle(): ?string
    {
        return $this->purpose
            ?? $this->title
            ?? $this->description
            ?? $this->notes
            ?? null;
    }

    /**
     * Default contextual payload for dynamic rule evaluation.
     *
     * @return array<string, mixed>
     */
    public function getApprovalContext(): array
    {
        return [];
    }

    /**
     * Default lifecycle handler updating the model's status column if present.
     */
    public function onApprovalStatusChanged(ApprovalRequestStatus $status, ?ApprovalRequest $request = null): void
    {
        $statusValue = match ($status) {
            ApprovalRequestStatus::Pending => 'pending_approval',
            ApprovalRequestStatus::Approved => 'approved',
            ApprovalRequestStatus::Rejected => 'rejected',
            ApprovalRequestStatus::Revision => 'revision_requested',
            ApprovalRequestStatus::Cancelled => 'cancelled',
        };

        if (array_key_exists('status', $this->getAttributes()) || $this->isFillable('status')) {
            $this->update(['status' => $statusValue]);
        }
    }
}
