<?php

namespace App\Repositories\Purchasing;

use App\Models\Purchasing\SupplierBankAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierBankAccountRepository
{
    public function all(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = SupplierBankAccount::with('supplier:id,supplier_code,name')->latest('id');

        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['is_primary']) && $filters['is_primary'] !== '') {
            $query->where('is_primary', filter_var($filters['is_primary'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                    ->orWhere('bank_account_number', 'like', "%{$search}%")
                    ->orWhere('bank_account_name', 'like', "%{$search}%")
                    ->orWhere('branch', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?SupplierBankAccount
    {
        return SupplierBankAccount::with('supplier:id,supplier_code,name')->find($id);
    }

    public function create(array $data): SupplierBankAccount
    {
        return SupplierBankAccount::create($data);
    }

    public function update(int $id, array $data): ?SupplierBankAccount
    {
        $account = SupplierBankAccount::find($id);
        if ($account) {
            $account->update($data);
        }

        return $account;
    }

    public function delete(int $id): ?bool
    {
        $account = SupplierBankAccount::find($id);

        return $account?->delete();
    }

    public function resetPrimary(int $supplierId, ?int $exceptId = null): void
    {
        $query = SupplierBankAccount::where('supplier_id', $supplierId)
            ->where('is_primary', true);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $query->update(['is_primary' => false]);
    }
}
