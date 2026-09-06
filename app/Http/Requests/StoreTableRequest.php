<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseCompanyRequest;

class StoreTableRequest extends BaseCompanyRequest
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
                'table_number' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('tables')->where(function ($query) {
                        return $query->where('company_id', $this->company_id);
                    }),
                ],
                'capacity' => ['required', 'integer', 'min:1'],
                'location' => ['nullable', 'string', 'max:255'],
                'is_active' => ['nullable', 'boolean'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'table_number.required' => 'The table number field is mandatory.',
                'table_number.unique' => 'A table with this number already exists for this company.',
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

