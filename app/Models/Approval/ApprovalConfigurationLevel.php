<?php

declare(strict_types=1);

namespace App\Models\Approval;

use App\Enums\Approval\ApprovalMode;
use App\Enums\Approval\ApproverScope;
use App\Models\Core\JobLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

#[Fillable([
    'approval_configuration_id',
    'step_order',
    'step_name',
    'approver_scope',
    'role_id',
    'job_level_id',
    'specific_user_id',
    'approval_mode',
    'can_be_skipped',
    'condition_type',
    'condition_value',
    'sla_hours',
])]
final class ApprovalConfigurationLevel extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'approver_scope' => ApproverScope::class,
            'approval_mode' => ApprovalMode::class,
            'can_be_skipped' => 'boolean',
            'condition_type' => 'string',
            'condition_value' => 'string',
            'sla_hours' => 'integer',
        ];
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(ApprovalConfiguration::class, 'approval_configuration_id');
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
}
