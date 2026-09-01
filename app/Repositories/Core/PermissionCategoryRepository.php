<?php

namespace App\Repositories\Core;

use App\Models\Core\PermissionCategory;

class PermissionCategoryRepository 
{
    public function all($search = null)
    {
        $query = PermissionCategory::orderBy('name', 'asc');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%");
            });
        }

        return $query->paginate(10);
    }
}