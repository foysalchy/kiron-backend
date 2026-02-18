<?php

namespace App\Http\Requests\Tax;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTaxRateRequest extends BaseCompanyRequest
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
        return array_merge($this->companyRules(), [
            'name'     => ['required', 'string', 'max:255'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
    }
     /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required'     => 'The Tax name is required',
                'tax_rate.required'   => 'The Tax rate is required',
            ]
        );
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
