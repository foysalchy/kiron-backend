<?php
namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRejoinRequest extends BaseCompanyRequest
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
                'employee_id'        => ['required', 'exists:employees,id'],
                'rejoin_date'        => ['required', 'date'],
                'appointment_letter' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
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
                'employee_id.required'      => 'Please select an employee.',
                'rejoin_date.required'      => 'Rejoin date is mandatory.',
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
