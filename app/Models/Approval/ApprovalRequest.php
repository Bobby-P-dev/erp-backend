<?php

declare(strict_types=1);

namespace App\Models\Approval;

use App\Enums\Approval\ApprovalLevelStatus;
use App\Enums\Approval\ApprovalRequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'approval_configuration_id',
    'approvable_type',
    'approvable_id',
    'document_number',
    'document_title',
    'requester_id',
    'current_step_order',
    'status',
    'total_amount',
    'submitted_at',
    'completed_at',
    'metadata',
])]
final class ApprovalRequest extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_step_order' => 'integer',
            'status' => ApprovalRequestStatus::class,
            'total_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(ApprovalConfiguration::class, 'approval_configuration_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(ApprovalRequestLevel::class)->orderBy('step_order');
    }

    public function currentLevel(): HasOne
    {
        return $this->hasOne(ApprovalRequestLevel::class)
            ->ofMany(['step_order' => 'min'], function ($query): void {
                $query->whereIn('status', [
                    ApprovalLevelStatus::Pending->value,
                    ApprovalLevelStatus::Revision->value,
                ]);
            });
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class);
    }

    public function canBeResubmitted(): bool
    {
        return $this->status === ApprovalRequestStatus::Revision;
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, [
            ApprovalRequestStatus::Approved,
            ApprovalRequestStatus::Rejected,
        ], true);
    }
}
