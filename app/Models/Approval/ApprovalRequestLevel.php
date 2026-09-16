<?php

declare(strict_types=1);

namespace App\Models\Approval;

use App\Enums\Approval\ApprovalLevelStatus;
use App\Enums\Approval\ApprovalMode;
use App\Enums\Approval\ApproverScope;
use App\Models\Core\Division;
use App\Models\Core\JobLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;

#[Fillable([
    'approval_request_id',
    'step_order',
    'step_name',
    'approver_scope',
    'approval_mode',
    'can_be_skipped',
    'required_approvers_count',
    'role_id',
    'job_level_id',
    'specific_user_id',
    'division_id',
    'status',
    'completed_at',
])]
final class ApprovalRequestLevel extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'status' => ApprovalLevelStatus::class,
            'approver_scope' => ApproverScope::class,
            'approval_mode' => ApprovalMode::class,
            'can_be_skipped' => 'boolean',
            'required_approvers_count' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class, 'job_level_id');
    }

    public function specificUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'specific_user_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class);
    }
}
