<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmployeeSalaryRequest extends FormRequest
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
        return [
            'salary_data'              => ['required', 'array'],
            'salary_data.*.pay_head_id' => ['required', 'exists:pay_heads,id'],
            'salary_data.*.type'        => ['required', 'in:addition,deduction'],
            'salary_data.*.amount'      => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'salary_data.required'                  => 'Salary data is required.',
            'salary_data.array'                     => 'Salary data must be an array.',
            'salary_data.*.pay_head_id.required'    => 'Each salary entry must have a pay head.',
            'salary_data.*.pay_head_id.exists'      => 'Selected pay head is invalid.',
            'salary_data.*.type.required'           => 'Each salary entry must have a type.',
            'salary_data.*.type.in'                 => 'Salary type must be either addition or deduction.',
            'salary_data.*.amount.required'         => 'Each salary entry must have an amount.',
            'salary_data.*.amount.numeric'          => 'Salary amount must be a number.',
            'salary_data.*.amount.min'              => 'Salary amount must be at least 0.',
        ];
    }

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