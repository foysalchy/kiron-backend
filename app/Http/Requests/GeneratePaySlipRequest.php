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
                'employee_ids' => ['required', 'array', 'min:1'],
                'employee_ids.*' => ['exists:employees,id'],
                'period' => ['required', 'string'], // e.g., "Jan-26 To Jun-26"
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
                'employee_ids.required' => 'Please select at least one employee.',
                'employee_ids.array' => 'Employee selection must be an array.',
                'period.required' => 'The payslip period is required.',
                'generated_date.required' => 'Generation date is required.',
                'generated_date.date' => 'Please provide a valid date.',
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
