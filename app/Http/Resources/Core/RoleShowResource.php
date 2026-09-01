<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $groupedPermissions = $this->permissions->groupBy(function ($permission) {
            return $permission->permissionCategory->name ?? 'Other';
        });

        $formattedPermissions = [];
        foreach ($groupedPermissions as $category => $permissions) {
            $formattedPermissions[] = [
                'category_name' => $category,
                'permissions' => $permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'label' => $permission->label,
                    ];
                })->values()->toArray()
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'grouped_permissions' => $formattedPermissions
        ];
    }
}
