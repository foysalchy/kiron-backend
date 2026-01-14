<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCellRequest extends FormRequest
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
        return [
            'company_id' => 'sometimes|required|exists:companies,id',
            'rack_id'    => 'sometimes|required|exists:racks,id',
            'name'       => 'sometimes|required|string|max:255',
            'status'     => 'sometimes|required|integer|in:0,1',
        ];
    }
    public function messages(): array
    {
        return [
            'company_id.exists' => 'The selected company is invalid.',
            'rack_id.exists'    => 'The selected rack does not exist.',
            'status.in'         => 'Status must be 1 for Active or 0 for Inactive.',
        ];
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
