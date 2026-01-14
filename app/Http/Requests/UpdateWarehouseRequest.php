<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateWarehouseRequest extends FormRequest
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
            'company_id'     => 'sometimes|required|exists:companies,id',
            'name' => 'sometimes|required|string|max:255',
            'location'       => 'nullable|string|max:500',
            'status'         => 'sometimes|required|integer|in:0,1',
        ];
    }
    public function messages(): array
    {
        return [
            'company_id.exists' => 'Selected company does not exist',
            'name.required' => 'Warehouse Name is required',
            'status.in' => 'Status must be 0 (Inactive) or 1 (Active)',
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
