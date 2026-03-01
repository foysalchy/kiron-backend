<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePositionRequest extends BaseCompanyRequest
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
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'string', 'max:20'],
                'pay_roll_id' => ['required', 'exists:pay_rolls,id'],
                'head_count' => ['required', 'integer', 'min:0'],
                'supervisor_id' => ['nullable', 'exists:employees,id'],
                'status' => ['nullable', 'integer', 'in:0,1'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'pay_roll_id.required'    => 'Please select a payroll.',
                'pay_roll_id.exists'      => 'The selected payroll is invalid.',
                'supervisor_id.exists'   => 'The selected supervisor does not exist.',
                'head_count.required'      => 'Head count is required.',
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
