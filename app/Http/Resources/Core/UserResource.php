<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->whenLoaded('employee', function () {
                return $this->employee->name; 
            }),
            'email' => $this->whenLoaded('employee', function () {
                return $this->employee->email; 
            }),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'all_permissions' => $this->when(isset($this->all_permissions), $this->all_permissions),
        ];
    }
}
