<?php

namespace App\Models\Core;

use Database\Factories\Core\AccountingSubcategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingSubcategory extends Model
{
    /** @use HasFactory<AccountingSubcategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'accounting_subcategories';

    protected $fillable = [
        'accounting_category_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'accounting_category_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AccountingCategory::class, 'accounting_category_id');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(AccountingAccount::class, 'accounting_subcategory_id');
    }
}
