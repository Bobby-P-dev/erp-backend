<?php

declare(strict_types=1);

namespace App\Enums\Approval;

enum ApprovalLevelStatus: string
{
    case Pending = 'pending';
    case Revision = 'revision';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Skipped = 'skipped';
}
