<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOfficeLocationRequest extends UpdateBaseCompanyRequest
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
                'location_name' => ['sometimes', 'required', 'string', 'max:255'],
                'address'       => ['sometimes', 'required', 'string', 'max:255'],
                'country'       => ['nullable', 'string', 'max:100'],
                'division'      => ['nullable', 'string', 'max:100'],
                'district'      => ['nullable', 'string', 'max:100'],
                'thana'         => ['nullable', 'string', 'max:100'],
                'description'   => ['nullable', 'string'],
                'status'        => ['sometimes', 'integer', 'in:0,1'],
            ]
        );
    }
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'location_name.required' => 'Location name is required',
                'address.required'       => 'Full address is required',
                'status.in'              => 'Status must be either 0 (Inactive) or 1 (Active)',
            ]
        );
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
