<?php

namespace App\Services\Core;

use App\Models\Core\Permission;
use App\Models\Core\PermissionCategory;

class PermissionService
{

    public function store(array $data)
    {
        $category = PermissionCategory::findOrFail($data['category_id']);

        $categoryName = strtolower(trim($category->name));
        $labelName = strtolower(trim($data['label']));

        $permissionName = $categoryName . '.' . $labelName;

        try {
            $permission = Permission::create([
                'name' => $permissionName,
                'label' => $data['label'],
                'permission_category_id' => $category->id,
            ]);

            return $permission;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update()
    {

    }

    public function delete()
    {

    }
}