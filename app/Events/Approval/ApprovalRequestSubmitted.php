<?php

declare(strict_types=1);

namespace App\Events\Approval;

use App\Models\Approval\ApprovalRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalRequestSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ApprovalRequest $approvalRequest,
        public Model $approvable
    ) {}
}
