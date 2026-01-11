<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AttributeGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(['Single', 'Multiple'])],
            'status' => ['integer'],
        ];
    }

    public function messages(): array
    {
      return [
            'company_id.required' => 'Company is required',
            'company_id.exists' => 'Selected company does not exist',
            'categoey.required' => 'category is required',
            'categoey.in' => 'category must be valied type',
            'name.required' => 'Name is required'
            
        ];
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
