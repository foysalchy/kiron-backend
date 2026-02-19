<?php

namespace App\Http\Requests\Tax;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTaxGroupRequest extends BaseCompanyRequest
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
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'name'        => ['required', 'string', 'max:255'],
            'sub_tax'     => ['required', 'array'],
            'sub_tax.*'   => ['required', 'string', 'distinct', 'exists:tax_rates,name'],
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
                'tax_rate_id.required' => 'The parent tax rate is required',
                'tax_rate_id.exists'   => 'The selected tax rate is invalid',
                'name.required'        => 'The Tax Group name is required',
                'sub_tax.required'     => 'At least one sub-tax name is required',
                'sub_tax.array'        => 'Sub-tax must be a valid array format',
                'sub_tax.*.required'   => 'Each sub-tax name is required',
                'sub_tax.*.string'     => 'Each sub-tax name must be a text value',
                'sub_tax.*.distinct'   => 'Sub-tax names must be unique in this group',
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
