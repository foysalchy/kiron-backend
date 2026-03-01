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
                'type'            => ['sometimes', 'required', 'string'],
                'pay_roll_id'    => ['sometimes', 'required',  'exists:pay_rolls,id'],
                'head_count'      => ['sometimes', 'required', 'integer', 'min:0'],
                'supervisor_id' => ['nullable', 'exists:employees,id'],
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
                'pay_roll_id.required' => 'Please select a payroll.',
                'pay_roll_id.exists'   => 'The selected payroll is invalid.',
                'supervisor_id.exists' => 'The selected supervisor does not exist.',
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
