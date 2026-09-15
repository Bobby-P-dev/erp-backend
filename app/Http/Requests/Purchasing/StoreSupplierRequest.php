<?php

namespace App\Http\Requests\Purchasing;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
        return [
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'supplier_code' => ['nullable', 'string', 'max:50', 'unique:suppliers,supplier_code'],
            'name' => ['required', 'string', 'max:255'],
            'supplier_type' => ['nullable', 'string', 'max:100'],
            'bussines_type' => ['nullable', 'string', 'max:100'],
            'company_category' => ['nullable', 'string', 'max:100'],
            'bussines_field' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'payment_term' => ['nullable', 'string', 'max:100'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'approval_status' => ['nullable', 'string', 'in:pending,approved,rejected'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
