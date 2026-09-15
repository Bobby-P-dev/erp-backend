<?php

namespace App\Models\Purchasing;

use App\Models\Core\AccountingAccount;
use App\Models\Core\AccountingCategory;
use App\Models\Core\AccountingSubcategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = 'items';

    protected $fillable = [
        'name',
        'code',
        'description',
        'item_type',
        'unit_id',
        'accounting_category_id',
        'accounting_subcategory_id',
        'accounting_account_id',
    ];

    protected $casts = [
        'accounting_category_id' => 'integer',
        'accounting_subcategory_id' => 'integer',
        'accounting_account_id' => 'integer',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function purchaseRequisitionItems(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class, 'item_id');
    }

    public function supplierItems(): HasMany
    {
        return $this->hasMany(SupplierItem::class, 'item_id');
    }

    public function accountingCategory(): BelongsTo
    {
        return $this->belongsTo(AccountingCategory::class, 'accounting_category_id');
    }

    public function accountingSubcategory(): BelongsTo
    {
        return $this->belongsTo(AccountingSubcategory::class, 'accounting_subcategory_id');
    }

    public function accountingAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }
}
