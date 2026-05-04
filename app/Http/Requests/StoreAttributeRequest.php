<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAttributeRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'attribute_group_id' => ['required', 'exists:attribute_groups,id'],
                'names'              => ['required', 'array', 'min:1'],
                'names.*'            => ['required', 'string', 'max:255'],
                'status'             => ['boolean'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'attribute_group_id.required' => 'Attribute group is required',
                'attribute_group_id.exists'   => 'Selected attribute group does not exist',
                'names.required'              => 'At least one attribute name is required',
                'names.*.required'            => 'Attribute name cannot be empty',
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
