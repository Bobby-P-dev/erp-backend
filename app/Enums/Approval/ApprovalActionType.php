<?php

declare(strict_types=1);

namespace App\Enums\Approval;

enum ApprovalActionType: string
{
    case Approve = 'approve';
    case Reject = 'reject';
    case RequestRevision = 'request_revision';
    case Resubmit = 'resubmit';
}
