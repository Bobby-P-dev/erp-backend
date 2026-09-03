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
                $q->where('name', 'like', "%{$search}%");

            });
        }

        return $query->paginate(10);
    }

    public function find($id)
    {
        return PermissionCategory::find($id);
    }

    public function create(array $data)
    {
        return PermissionCategory::create($data);
    }

    public function update(array $data, $id)
    {
        return PermissionCategory::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return PermissionCategory::where('id', $id)->delete();
    }

    public function searchPermissionCategory($search = null)
    {
        $query = PermissionCategory::select('id', 'name');

        if (filled($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->take(5)->get();
    }
}