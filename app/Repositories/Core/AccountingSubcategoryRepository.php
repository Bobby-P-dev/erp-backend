<?php

namespace App\Repositories\Core;

use App\Models\Core\AccountingSubcategory;

class AccountingSubcategoryRepository
{
    public function all($search = null, array $filter = [])
    {
        $query = AccountingSubcategory::with('category:id,code,name')->latest('id');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filter['accounting_category_id'])) {
            $query->where('accounting_category_id', $filter['accounting_category_id']);
        }

        if (isset($filter['is_active']) && filled($filter['is_active'])) {
            $query->where('is_active', filter_var($filter['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->paginate(10);
    }

    public function find($id)
    {
        return AccountingSubcategory::with(['category', 'accounts'])->find($id);
    }

    public function create(array $data): AccountingSubcategory
    {
        return AccountingSubcategory::create($data);
    }

    public function update(array $data, $id): ?AccountingSubcategory
    {
        $subcategory = AccountingSubcategory::find($id);
        if ($subcategory) {
            $subcategory->update($data);
        }

        return $subcategory;
    }

    public function delete($id): ?bool
    {
        $subcategory = AccountingSubcategory::find($id);

        return $subcategory?->delete();
    }

    public function search($search = null, $categoryId = null)
    {
        $query = AccountingSubcategory::with('category:id,code,name')
            ->select('id', 'accounting_category_id', 'code', 'name')
            ->where('is_active', true);

        if (! empty($categoryId)) {
            $query->where('accounting_category_id', $categoryId);
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
