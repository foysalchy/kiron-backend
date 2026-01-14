<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCellRequest extends FormRequest
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
            'company_id' => 'required|exists:companies,id',
            'rack_id'    => 'required|exists:racks,id',
            'name'       => 'required|string|max:255',
            'status'     => 'required|integer|in:0,1',
        ];
    }
    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'company_id.exists' => 'The selected company is invalid.',
            'rack_id.exists'    => 'The selected rack does not exist.',
            'name.required'     => 'The cell name is mandatory.',
            'status.required'   => 'Please specify if the cell is active (1) or inactive (0).',
        ];
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
