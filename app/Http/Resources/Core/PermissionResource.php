<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'permission_category_id' => $this->permission_category_id,
            'guard_name' => $this->guard_name,
            'category' => new PermissionCategoryResource($this->whenLoaded('permissionCategory')),
        ];
    }
}
