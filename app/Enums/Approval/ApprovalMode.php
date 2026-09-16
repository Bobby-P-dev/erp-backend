<?php

declare(strict_types=1);

namespace App\Enums\Approval;

enum ApprovalMode: string
{
    case Any = 'any';
    case All = 'all';
}
