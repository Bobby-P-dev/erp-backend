<?php

namespace App\Models\Purchasing;

use Database\Factories\Purchasing\SupplierItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierItem extends Model
{
    /** @use HasFactory<SupplierItemFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'supplier_items';

    protected $fillable = [
        'supplier_id',
        'item_id',
        'supplier_item_code',
        'supplier_item_name',
        'reference_url',
        'default_price',
        'currency',
        'minimum_order_quantity',
        'lead_time_days',
        'is_active',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'minimum_order_quantity' => 'decimal:4',
        'lead_time_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
