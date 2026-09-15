<?php

namespace App\Http\Resources\Purchasing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierItemResource extends JsonResource
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
                    'email' => $this->supplier->email,
                    'phone' => $this->supplier->phone,
                    'is_active' => $this->supplier->is_active,
                ];
            }),
            'item_id' => $this->item_id,
            'item' => $this->whenLoaded('item', function () {
                return [
                    'id' => $this->item->id,
                    'code' => $this->item->code,
                    'name' => $this->item->name,
                    'description' => $this->item->description,
                    'item_type' => $this->item->item_type,
                    'unit_id' => $this->item->unit_id,
                ];
            }),
            'supplier_item_code' => $this->supplier_item_code,
            'supplier_item_name' => $this->supplier_item_name,
            'reference_url' => $this->reference_url,
            'default_price' => $this->default_price !== null ? (float) $this->default_price : null,
            'currency' => $this->currency,
            'minimum_order_quantity' => $this->minimum_order_quantity !== null ? (float) $this->minimum_order_quantity : null,
            'lead_time_days' => $this->lead_time_days,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
