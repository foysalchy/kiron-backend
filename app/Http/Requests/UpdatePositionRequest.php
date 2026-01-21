<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends UpdateBaseCompanyRequest
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
                'name'            => ['sometimes', 'required', 'string', 'max:255'],
                'type'            => ['sometimes', 'required', 'string', Rule::in(['shared', 'single'])],
                'payroll_name'    => ['sometimes', 'required', 'string', 'exists:pay_rolls,name'],
                'head_count'      => ['sometimes', 'required', 'integer', 'min:0'],
                'supervisor_name' => ['nullable', 'string', 'exists:employees,name'],
                'status'          => ['sometimes', 'required', 'integer', 'in:0,1'],
            ]
        );
    }
    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required'         => 'Position name cannot be empty.',
                'payroll_name.required' => 'Please select a payroll.',
                'payroll_name.exists'   => 'The selected payroll name is invalid.',
                'supervisor_name.exists' => 'The selected supervisor name does not exist.',
                'head_count.integer'    => 'Head count must be a valid number.',
            ]
        );
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
