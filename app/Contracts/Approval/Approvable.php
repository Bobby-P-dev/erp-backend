<?php

declare(strict_types=1);

namespace App\Contracts\Approval;

use App\Enums\Approval\ApprovalRequestStatus;
use App\Models\Approval\ApprovalRequest;

interface Approvable
{
    /**
     * Get the company ID associated with the document, if any.
     */
    public function getApprovalCompanyId(): ?int;

    /**
     * Get the division ID associated with the document, if any.
     */
    public function getApprovalDivisionId(): ?int;

    /**
     * Get the transaction amount if this document is financial, or null if non-financial.
     */
    public function getApprovalAmount(): ?float;

    /**
     * Get the human-readable document number (e.g. PR-2026-0001, PO/2026/09/001, SO-8821).
     */
    public function getApprovalDocumentNumber(): ?string;

    /**
     * Get a human-readable title / purpose / summary of the document.
     */
    public function getApprovalDocumentTitle(): ?string;

    /**
     * Additional payload/attributes used for conditional level evaluation.
     *
     * @return array<string, mixed>
     */
    public function getApprovalContext(): array;

    /**
     * Lifecycle callback invoked whenever the approval state changes.
     */
    public function onApprovalStatusChanged(ApprovalRequestStatus $status, ?ApprovalRequest $request = null): void;
}
