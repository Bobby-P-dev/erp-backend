<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $table = 'units';

    protected $fillable = [
        'name',
        'code',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'unit_id');
    }

    public function purchaseRequisitionItems(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class, 'unit_id');
    }
}
