<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use function PHPUnit\Framework\returnArgument;

class StorePayRollRequest extends BaseCompanyRequest
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
                'payroll_type' => ['required', Rule::in(['fixed', 'hourly', 'contractual'])],
                'payment_type' => ['required', Rule::in(['monthly', 'weekly', 'bi-weekly', 'daily'])],
                'status'      => ['nullable', 'integer', 'in:0,1'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required' => 'Payroll name is required',
                'payroll_type.required' => 'Payroll type is required',
                'payment_type.required' => 'Payment type is required',
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
