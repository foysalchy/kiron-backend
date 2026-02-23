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
            'name'           => ['sometimes', 'string', 'max:255'],
            'tax_rate_ids'   => ['required', 'array', 'min:1'],
            'tax_rate_ids.*' => ['required', 'integer', 'exists:tax_rates,id'],
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
                'name.string'               => 'The tax group name must be a valid string',
                'name.max'                  => 'The tax group name must not exceed 255 characters',
                'tax_rate_ids.required'     => 'At least one tax rate must be selected',
                'tax_rate_ids.array'        => 'Tax rates must be provided as an array',
                'tax_rate_ids.min'          => 'At least one tax rate must be selected',
                'tax_rate_ids.*.required'   => 'Each tax rate ID is required',
                'tax_rate_ids.*.integer'    => 'Each tax rate ID must be a valid integer',
                'tax_rate_ids.*.exists'     => 'One or more selected tax rates do not exist',
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
