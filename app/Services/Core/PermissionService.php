<?php

namespace App\Services\Core;

use App\Repositories\Core\PermissionCategoryRepository;
use App\Repositories\Core\PermissionRepository;
use RuntimeException;

class PermissionService
{
    public function __construct(
        protected PermissionRepository $permissionRepository,
        protected PermissionCategoryRepository $permissionCategoryRepository
    ) {}

    public function store(array $data)
    {
        $category = $this->permissionCategoryRepository->find($data['category_id']);
        if (! $category) {
            throw new RuntimeException('Permission category not found.');
        }

        $categoryName = strtolower(trim($category->name));
        $labelName = strtolower(trim($data['label']));

        $permissionName = $categoryName.'.'.$labelName;

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

        $category = $this->permissionCategoryRepository->find($categoryId);
        if (! $category) {
            throw new RuntimeException('Permission category not found.');
        }

        $categoryName = strtolower(trim($category->name));

        $permissionName = $categoryName.'.'.$labelName;

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
