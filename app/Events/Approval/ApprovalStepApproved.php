<?php

declare(strict_types=1);

namespace App\Events\Approval;

use App\Models\Approval\ApprovalAction;
use App\Models\Approval\ApprovalRequest;
use App\Models\Approval\ApprovalRequestLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalStepApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ApprovalRequest $approvalRequest,
        public ApprovalRequestLevel $level,
        public ApprovalAction $action,
        public Model $approvable
    ) {}
}
