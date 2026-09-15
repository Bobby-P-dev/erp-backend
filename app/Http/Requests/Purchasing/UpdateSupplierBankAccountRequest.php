<?php

namespace App\Http\Requests\Purchasing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierBankAccountRequest extends FormRequest
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
            'supplier_id' => ['sometimes', 'required', 'integer', 'exists:suppliers,id'],
            'bank_name' => ['sometimes', 'required', 'string', 'max:100'],
            'bank_account_number' => ['sometimes', 'required', 'string', 'max:50'],
            'bank_account_name' => ['sometimes', 'required', 'string', 'max:150'],
            'branch' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
