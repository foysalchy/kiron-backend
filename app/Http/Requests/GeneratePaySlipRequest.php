<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GeneratePaySlipRequest extends BaseCompanyRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'employees' => ['required', 'array', 'min:1'],
                'employees.*.employee_id' => ['required', 'exists:employees,id'],
                'employees.*.period_id'   => ['required', 'exists:periods,id'],
                'generated_date' => ['required', 'date'],
                'status' => ['integer', 'nullable'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'employees.required' => 'Please select at least one employee with their period.',
                'employees.*.employee_id.exists' => 'Selected employee is invalid.',
                'employees.*.period_id.required' => 'Each employee must have a selected period.',
                'generated_date.required' => 'Generation date is required.',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
