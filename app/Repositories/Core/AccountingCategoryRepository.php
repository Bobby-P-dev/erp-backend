<?php

namespace App\Repositories\Core;

use App\Models\Core\AccountingCategory;

class AccountingCategoryRepository
{
    public function all($search = null, array $filter = [])
    {
        $query = AccountingCategory::query()->latest('id');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (isset($filter['is_active']) && filled($filter['is_active'])) {
            $query->where('is_active', filter_var($filter['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->paginate(10);
    }

    public function find($id)
    {
        return AccountingCategory::with('subcategories')->find($id);
    }

    public function create(array $data): AccountingCategory
    {
        return AccountingCategory::create($data);
    }

    public function update(array $data, $id): ?AccountingCategory
    {
        $category = AccountingCategory::find($id);
        if ($category) {
            $category->update($data);
        }

        return $category;
    }

    public function delete($id): ?bool
    {
        $category = AccountingCategory::find($id);

        return $category?->delete();
    }

    public function search($search = null)
    {
        $query = AccountingCategory::select('id', 'code', 'name')->where('is_active', true);

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->take(10)->get();
    }
}
