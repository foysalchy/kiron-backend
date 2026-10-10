<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCustomerGroupRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->companyRules(), [
            'name' => 'required|string|max:255',
            'filter_type' => 'required',
            'filter_parameters' => 'nullable|array',
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'integer|exists:parties,id',
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'name.required'   => 'Group name is required',
            'filter_type.required' => 'Filter Type  is required',

        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
