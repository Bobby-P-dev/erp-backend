<?php

namespace App\Models\Core;

use Database\Factories\Core\AccountingAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingAccount extends Model
{
    /** @use HasFactory<AccountingAccountFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'accounting_accounts';

    protected $fillable = [
        'accounting_subcategory_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'accounting_subcategory_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(AccountingSubcategory::class, 'accounting_subcategory_id');
    }
}
