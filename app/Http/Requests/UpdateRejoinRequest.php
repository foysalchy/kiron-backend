<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRejoinRequest extends UpdateBaseCompanyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'employee_id'        => ['sometimes', 'exists:employees,id'],
                'rejoin_date'        => ['sometimes', 'date'],
                'appointment_letter' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:2048'],
            ]
        );
    }
    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'employee_id.exists'      => 'The selected employee does not exist.',
                'rejoin_date.date'        => 'Rejoin date must be a valid date.',
                'appointment_letter.mimes'  => 'Letter must be a PDF, JPG, or PNG file.',
                'appointment_letter.max'    => 'File size cannot exceed 2MB.',
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
