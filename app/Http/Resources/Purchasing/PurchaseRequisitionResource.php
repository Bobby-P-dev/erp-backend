<?php

namespace App\Http\Resources\Purchasing;

use App\Http\Resources\Core\CompanyResource;
use App\Http\Resources\Core\DivisionResource;
use App\Http\Resources\Core\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequisitionResource extends JsonResource
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
            'pr_number' => $this->pr_number,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'division_id' => $this->division_id,
            'division' => new DivisionResource($this->whenLoaded('division')),
            'requester_id' => $this->requester_id,
            'requester' => new UserResource($this->whenLoaded('requester')),
            'request_date' => $this->request_date?->format('Y-m-d') ?? (string) $this->request_date,
            'required_date' => $this->required_date?->format('Y-m-d') ?? $this->required_date,
            'purpose' => $this->purpose,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'items' => PurchaseRequisitionItemResource::collection(
                $this->whenLoaded('items', fn () => $this->items, fn () => $this->whenLoaded('purchaseRequisitionItems'))
            ),
        ];
    }
}
