<?php

namespace App\Http\Resources\Purchasing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'name' => $this->name,
            'company_id' => $this->whenHas('company_id'),
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'code' => $this->company->code,
                    'name' => $this->company->name,
                ];
            }),
            'supplier_code' => $this->whenHas('supplier_code'),
            'supplier_type' => $this->whenHas('supplier_type'),
            'bussines_type' => $this->whenHas('bussines_type'),
            'company_category' => $this->whenHas('company_category'),
            'bussines_field' => $this->whenHas('bussines_field'),
            'address' => $this->whenHas('address'),
            'city' => $this->whenHas('city'),
            'region' => $this->whenHas('region'),
            'postal_code' => $this->whenHas('postal_code'),
            'country' => $this->whenHas('country'),
            'phone' => $this->whenHas('phone'),
            'email' => $this->whenHas('email'),
            'tax_id' => $this->whenHas('tax_id'),
            'payment_term' => $this->whenHas('payment_term'),
            'lead_time_days' => $this->whenHas('lead_time_days'),
            'approval_status' => $this->whenHas('approval_status'),
            'approved_by' => $this->whenHas('approved_by'),
            'approved_at' => $this->whenHas('approved_at', function () {
                return $this->approved_at?->toISOString();
            }),
            'is_active' => $this->whenHas('is_active', function () {
                return (bool) $this->is_active;
            }),
            'contacts' => SupplierContactResource::collection($this->whenLoaded('contacts')),
            'bank_accounts' => SupplierBankAccountResource::collection($this->whenLoaded('bankAccounts')),
            'documents' => SupplierDocumentResource::collection($this->whenLoaded('documents')),
            'created_at' => $this->whenHas('created_at', function () {
                return $this->created_at?->toISOString();
            }),
            'updated_at' => $this->whenHas('updated_at', function () {
                return $this->updated_at?->toISOString();
            }),
        ];
    }
}
