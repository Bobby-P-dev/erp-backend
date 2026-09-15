<?php

namespace App\Repositories\Core;

use App\Models\Core\AccountingAccount;

class AccountingAccountRepository
{
    public function all($search = null, array $filter = [])
    {
        $query = AccountingAccount::with('subcategory.category:id,code,name')->latest('id');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('subcategory', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filter['accounting_subcategory_id'])) {
            $query->where('accounting_subcategory_id', $filter['accounting_subcategory_id']);
        }

        if (isset($filter['is_active']) && filled($filter['is_active'])) {
            $query->where('is_active', filter_var($filter['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->paginate(10);
    }

    public function find($id)
    {
        return AccountingAccount::with('subcategory.category')->find($id);
    }

    public function create(array $data): AccountingAccount
    {
        return AccountingAccount::create($data);
    }

    public function update(array $data, $id): ?AccountingAccount
    {
        $account = AccountingAccount::find($id);
        if ($account) {
            $account->update($data);
        }

        return $account;
    }

    public function delete($id): ?bool
    {
        $account = AccountingAccount::find($id);

        return $account?->delete();
    }

    public function search($search = null, $subcategoryId = null)
    {
        $query = AccountingAccount::with('subcategory:id,code,name')
            ->select('id', 'accounting_subcategory_id', 'code', 'name')
            ->where('is_active', true);

        if (! empty($subcategoryId)) {
            $query->where('accounting_subcategory_id', $subcategoryId);
        }

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->take(10)->get();
    }
}
