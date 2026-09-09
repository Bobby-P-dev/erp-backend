<?php

namespace App\Models\Purchasing;

use App\Models\Core\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'company_id',
        'supplier_code',
        'name',
        'supplier_type',
        'bussines_type',
        'company_category',
        'bussines_field',
        'address',
        'city',
        'region',
        'postal_code',
        'country',
        'phone',
        'email',
        'tax_id',
        'payment_term',
        'lead_time_days',
        'approval_status',
        'approved_by',
        'approved_at',
        'is_active',
    ];

    protected $casts = [
        'lead_time_days' => 'integer',
        'approved_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class, 'supplier_id');
    }

    public function supplierContacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class, 'supplier_id');
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(SupplierBankAccount::class, 'supplier_id');
    }

    public function supplierBankAccounts(): HasMany
    {
        return $this->hasMany(SupplierBankAccount::class, 'supplier_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SupplierDocument::class, 'supplier_id');
    }

    public function supplierDocuments(): HasMany
    {
        return $this->hasMany(SupplierDocument::class, 'supplier_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'supplier_id');
    }
}
