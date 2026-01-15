<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\UpdateBaseCompanyRequest;

class UpdateAreaRequest extends UpdateBaseCompanyRequest
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
                'warehouse_id' => ['sometimes', 'required', 'exists:warehouses,id'],
                'name'         => ['sometimes', 'required', 'string', 'max:255'],
                'status'       => ['sometimes', 'required', 'integer', 'in:0,1'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'warehouse_id.exists' => 'The selected warehouse does not exist.',
                'name.required'       => 'The name field is mandatory.',
                'status.required'     => 'Status must be active (1) or inactive (0).',
            ]
        );
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ],422)
        );
    }
}
