<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierBankAccount extends Model
{
    use SoftDeletes;

    protected $table = 'supplier_bank_accounts';

    protected $fillable = [
        'supplier_id',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'branch',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
