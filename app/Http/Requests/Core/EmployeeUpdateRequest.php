<?php

namespace App\Http\Requests\Core;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
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
            'name' => 'sometimes|required',
            'nik' => [
                'sometimes',
                'required',
                Rule::unique('employees')->where(function ($query) {
                    return $query->where('company_id', $this->company_id ?? null);
                })->ignore($this->route('id'))
            ],
            'company_id' => 'sometimes|required|exists:companies,id',
            'division_id' => 'sometimes|required|exists:divisions,id',
            'position_id' => 'nullable|exists:positions,id',
            'job_level_id' => 'nullable|exists:job_levels,id',
            'email' => 'nullable|email',
            'is_active' => 'nullable|boolean',
        ];
    }
}
