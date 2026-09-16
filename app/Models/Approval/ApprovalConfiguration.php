<?php

declare(strict_types=1);

namespace App\Models\Approval;

use App\Models\Core\Company;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'document_type',
    'code',
    'name',
    'min_amount',
    'max_amount',
    'is_active',
    'description',
])]
final class ApprovalConfiguration extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(ApprovalConfigurationLevel::class)->orderBy('step_order');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class);
    }

    public function isUniversal(): bool
    {
        return $this->min_amount === null && $this->max_amount === null;
    }
}
