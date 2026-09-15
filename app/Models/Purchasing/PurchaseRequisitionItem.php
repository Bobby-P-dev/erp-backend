<?php

namespace App\Models\Purchasing;

use App\Models\Core\AccountingAccount;
use App\Models\Core\AccountingCategory;
use App\Models\Core\AccountingSubcategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequisitionItem extends Model
{
    protected $table = 'purchase_requestion_items';

    protected $fillable = [
        'purchase_requisition_id',
        'item_id',
        'unit_id',
        'quantity',
        'accounting_category_id',
        'accounting_subcategory_id',
        'accounting_account_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'accounting_category_id' => 'integer',
        'accounting_subcategory_id' => 'integer',
        'accounting_account_id' => 'integer',
    ];

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
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
