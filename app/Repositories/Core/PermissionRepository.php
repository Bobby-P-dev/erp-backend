<?php

namespace App\Repositories\Core;

use App\Models\Core\Permission;

class PermissionRepository
{
    public function all($search = null, $perPage = 10)
    {
        $query = Permission::select('id', 'name', 'label', 'permission_category_id')->with('permissionCategory:id,name')->latest();

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function searchPermission($search = null)
    {
        $query = Permission::with('permissionCategory:id,name')->select('id', 'name', 'label', 'permission_category_id');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%");
            });
        }

        return $query->take(5)->get();
    }

    public function find($id)
    {
        return Permission::find($id);
    }

    public function create(array $data)
    {
        return Permission::create($data);
    }

    public function update(array $data, $id)
    {
        return Permission::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return Permission::where('id', $id)->delete();
    }
}
