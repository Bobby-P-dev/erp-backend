<?php

namespace App\Http\Requests\Purchasing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequisitionRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => [
                'required',
                'integer',
                Rule::exists('companies', 'id')->where(function ($query) {
                    $query->where('is_active', true)->whereNull('deleted_at');
                }),
            ],
            'division_id' => [
                'required',
                'integer',
                Rule::exists('divisions', 'id')->where(function ($query) {
                    $query->where('company_id', $this->input('company_id'))
                        ->where('is_active', true)
                        ->whereNull('deleted_at');
                }),
            ],
            'requester_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
            ],
            'request_date' => [
                'required',
                'date',
            ],
            'required_date' => [
                'required',
                'date',
                'after_or_equal:request_date',
            ],
            'purpose' => [
                'required',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.item_id' => [
                'required',
                'integer',
                Rule::exists('items', 'id')->whereNull('deleted_at'),
            ],
            'items.*.unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id'),
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999999999.999',
            ],
            'items.*.notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'company_id' => 'company',
            'division_id' => 'division',
            'requester_id' => 'requester',
            'request_date' => 'tanggal permintaan',
            'required_date' => 'tanggal kebutuhan',
            'purpose' => 'tujuan pengadaan',
            'notes' => 'catatan',
            'items' => 'detail item',
            'items.*.item_id' => 'item',
            'items.*.unit_id' => 'satuan',
            'items.*.quantity' => 'jumlah',
            'items.*.notes' => 'catatan item',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'Company wajib dipilih.',
            'company_id.exists' => 'Company yang dipilih tidak valid atau tidak aktif.',
            'division_id.required' => 'Division wajib dipilih.',
            'division_id.exists' => 'Division yang dipilih tidak sesuai dengan Company atau tidak aktif.',
            'requester_id.required' => 'Requester wajib dipilih.',
            'requester_id.exists' => 'Requester tidak valid.',
            'request_date.required' => 'Tanggal permintaan wajib diisi.',
            'request_date.date' => 'Format tanggal permintaan tidak valid.',
            'required_date.required' => 'Tanggal kebutuhan wajib diisi.',
            'required_date.date' => 'Format tanggal kebutuhan tidak valid.',
            'required_date.after_or_equal' => 'Tanggal kebutuhan tidak boleh lebih awal dari tanggal permintaan.',
            'purpose.required' => 'Tujuan pengadaan wajib diisi.',
            'items.required' => 'Detail Purchase Requisition minimal harus memiliki satu item.',
            'items.array' => 'Format detail item tidak valid.',
            'items.min' => 'Detail Purchase Requisition minimal harus memiliki satu item.',
            'items.*.item_id.required' => 'Item wajib dipilih.',
            'items.*.item_id.exists' => 'Item yang dipilih tidak valid.',
            'items.*.unit_id.required' => 'Satuan (unit) wajib dipilih.',
            'items.*.unit_id.exists' => 'Satuan (unit) yang dipilih tidak valid.',
            'items.*.quantity.required' => 'Quantity wajib diisi.',
            'items.*.quantity.numeric' => 'Quantity harus berupa angka.',
            'items.*.quantity.gt' => 'Quantity harus lebih besar dari 0.',
        ];
    }
}
