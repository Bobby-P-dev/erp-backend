<?php

namespace App\Services\Core;

use App\Models\Core\PermissionCategory;
use App\Repositories\Core\PermissionRepository;

class PermissionService
{
    protected PermissionRepository $permissionRepository;

    public function __construct()
    {
        $this->permissionRepository = new PermissionRepository();
    }

    public function store(array $data)
    {
        $category = PermissionCategory::findOrFail($data['category_id']);

        $categoryName = strtolower(trim($category->name));
        $labelName = strtolower(trim($data['label']));

        $permissionName = $categoryName . '.' . $labelName;

        return $this->permissionRepository->create([
            'name' => $permissionName,
            'label' => $data['label'],
            'permission_category_id' => $category->id,
            'guard_name' => 'web',
        ]);
    }

    public function update(array $data, $id)
    {
        $permission = $this->permissionRepository->find($id);
        
        $categoryId = $data['category_id'] ?? $permission->permission_category_id;
        $labelName = strtolower(trim($data['label'] ?? $permission->label));
        
        $category = PermissionCategory::findOrFail($categoryId);
        $categoryName = strtolower(trim($category->name));

        $permissionName = $categoryName . '.' . $labelName;

        $updateData = [
            'name' => $permissionName,
            'permission_category_id' => $category->id,
        ];
        
        if (isset($data['label'])) {
            $updateData['label'] = $data['label'];
        }

        $this->permissionRepository->update($updateData, $id);

        return $this->permissionRepository->find($id);
    }
}