<?php

namespace App\Http\Resources\Core;

use App\Http\Resources\Purchasing\SupplierResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $name = null;
        $email = null;

        if ($this->relationLoaded('employee') && $this->employee) {
            $name = $this->employee->name;
            $email = $this->employee->email;
        } elseif ($this->relationLoaded('supplier') && $this->supplier) {
            $name = $this->supplier->name;
            $email = $this->supplier->email;
        }

        return [
            'id' => $this->id,
            'account_type' => $this->account_type ?? ($this->supplier_id ? 'supplier' : 'employee'),
            'name' => $name,
            'email' => $email,
            'employee' => $this->whenLoaded('employee', function () {
                return $this->employee ? new EmployeeResource($this->employee) : null;
            }),
            'supplier' => $this->whenLoaded('supplier', function () {
                return $this->supplier ? new SupplierResource($this->supplier) : null;
            }),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'all_permissions' => $this->when(isset($this->all_permissions), $this->all_permissions),
        ];
    }
}
