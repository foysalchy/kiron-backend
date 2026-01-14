<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRackRequest extends FormRequest
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
            'company_id'   => 'sometimes|required|exists:companies,id',
            'area_id'      => 'sometimes|required|exists:areas,id',
            'name'         => 'sometimes|required|string|max:255',
            'status'       => 'sometimes|integer|in:0,1',
        ];
    }
    public function messages(): array
    {
        return [
            'company_id.exists' => 'Selected company is invalid.',
            'area_id.exists'    => 'The selected area does not exist.',
            'name.required'     => 'The name field is mandatory.',
            'status.in'         => 'Status must be 1 (Active) or 0 (Inactive).',
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
