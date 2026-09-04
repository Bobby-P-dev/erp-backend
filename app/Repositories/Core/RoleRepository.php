<?php

namespace App\Repositories\Core;

use Spatie\Permission\Models\Role;

class RoleRepository
{
    public function all($search = null)
    {
        $query = Role::select('id', 'name')->latest();

        if (filled($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate(10);
    }

    public function searchRole($search = null)
    {
        $query = Role::select('id', 'name');

        if (filled($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->take(5)->get();
    }

    public function find($id)
    {
        return Role::with('permissions.permissionCategory')->find($id);
    }

    public function create(array $data)
    {
        return Role::create($data);
    }

    public function update(array $data, $id)
    {
        return Role::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return Role::where('id', $id)->delete();
    }

    public function syncPermissions($id, array $permissions)
    {
        $role = Role::findOrFail($id);
        $role->syncPermissions($permissions);
        return $role->load('permissions');
    }
}
