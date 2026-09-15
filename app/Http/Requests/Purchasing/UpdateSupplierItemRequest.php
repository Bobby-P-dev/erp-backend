<?php

namespace App\Http\Requests\Purchasing;

use App\Models\Purchasing\SupplierItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $supplierItemId = $this->route('id') ?? $this->route('supplier_item');
        $existing = $supplierItemId ? SupplierItem::find($supplierItemId) : null;
        $supplierId = $this->input('supplier_id', $existing?->supplier_id);

        return [
            'supplier_id' => ['sometimes', 'required', 'integer', 'exists:suppliers,id'],
            'item_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:items,id',
                Rule::unique('supplier_items', 'item_id')
                    ->ignore($supplierItemId)
                    ->where(fn ($query) => $query->where('supplier_id', $supplierId)->whereNull('deleted_at')),
            ],
            'supplier_item_code' => ['nullable', 'string', 'max:100'],
            'supplier_item_name' => ['nullable', 'string', 'max:255'],
            'reference_url' => ['nullable', 'string', 'url', 'max:1000'],
            'default_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'minimum_order_quantity' => ['nullable', 'numeric', 'min:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'item_id.unique' => 'This item has already been assigned to the selected supplier.',
        ];
    }
}
