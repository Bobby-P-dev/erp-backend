<?php

namespace App\Models\Core;

use Database\Factories\Core\AccountingCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingCategory extends Model
{
    /** @use HasFactory<AccountingCategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'accounting_categories';

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(AccountingSubcategory::class, 'accounting_category_id');
    }
}
