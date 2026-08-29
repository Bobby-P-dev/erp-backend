<?php

namespace App\Http\Resources\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'division_id' => $this->division_id,
            'position_id' => $this->position_id,
            'nik' => $this->nik,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'division' => new DivisionResource($this->whenLoaded('division')),
            'position' => new PositionResource($this->whenLoaded('position')),
        ];
    }
}
