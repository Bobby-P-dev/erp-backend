<?php

namespace App\Http\Resources\Purchasing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierDocumentResource extends JsonResource
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
            'supplier_id' => $this->supplier_id,
            'supplier' => $this->whenLoaded('supplier', function () {
                return [
                    'id' => $this->supplier->id,
                    'supplier_code' => $this->supplier->supplier_code,
                    'name' => $this->supplier->name,
                ];
            }),
            'file_id' => $this->file_id,
            'file' => $this->whenLoaded('file'),
            'document_number' => $this->document_number,
            'document_type' => $this->document_type,
            'issue_date' => $this->issue_date?->format('Y-m-d') ?? (string) $this->issue_date,
            'expiry_date' => $this->expiry_date?->format('Y-m-d') ?? (string) $this->expiry_date,
            'is_active' => (bool) $this->is_active,
            'is_verified' => (bool) $this->is_verified,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at?->toISOString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
