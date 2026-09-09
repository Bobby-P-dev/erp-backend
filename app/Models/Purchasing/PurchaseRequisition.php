<?php

namespace App\Models\Purchasing;

use App\Models\Core\Company;
use App\Models\Core\Division;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model
{
    use SoftDeletes;

    protected $table = 'purchase_requestions';

    protected $fillable = [
        'company_id',
        'division_id',
        'requester_id',
        'pr_number',
        'request_date',
        'required_date',
        'status',
        'purpose',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'required_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class, 'purchase_requisition_id');
    }

    public function purchaseRequisitionItems(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class, 'purchase_requisition_id');
    }
}
