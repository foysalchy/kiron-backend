<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseCompanyRequest;

class StoreAreaRequest extends BaseCompanyRequest
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
                'warehouse_id' => ['required', 'exists:warehouses,id'],
                'name'         => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('areas')->where(function ($query) {
                        return $query->where('warehouse_id', $this->warehouse_id);
                    }),
                ],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'warehouse_id.exists' => 'The selected warehouse does not exist.',
                'name.required'       => 'The area name field is mandatory.',
                'name.unique'         => 'An area with this name already exists in the selected warehouse.',
            ]
        );
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