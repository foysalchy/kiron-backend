<?php

namespace App\Http\Requests\Tax;

use App\Http\Requests\BaseCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTaxGroupRequest extends BaseCompanyRequest
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
            'name'    => ['sometimes', 'string', 'max:255'],
            'sub_tax' => ['sometimes', 'array'],
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
                'name.string'       => 'The tax group name must be a string.',
                'tax_rate_id.exists' => 'The selected parent tax rate is invalid.',
                'sub_tax.array'      => 'The sub-tax must be an array format.',
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
