<?php

declare(strict_types=1);

namespace App\Models\Purchasing;

use App\Contracts\Approval\Approvable;
use App\Models\Approval\Concerns\HasApprovals;
use App\Models\Core\Company;
use App\Models\Core\Division;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'division_id',
    'supplier_id',
    'po_number',
    'po_date',
    'total_amount',
    'status',
    'notes',
])]
final class PurchaseOrder extends Model implements Approvable
{
    use HasApprovals, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'po_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
