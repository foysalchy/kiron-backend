<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\BaseCompanyRequest;

class StoreAreaRequest extends BaseCompanyRequest
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
                'warehouse_id' => ['required', 'exists:warehouses,id'],
                'name'         => ['required', 'string', 'max:255'],
            
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
