<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLeadRequest extends UpdateBaseCompanyRequest
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
                'lead_source_id' => ['sometimes', 'exists:lead_sources,id'],
                'lead_status_id' => ['sometimes', 'exists:lead_statuses,id'],
                'full_name'      => ['sometimes', 'string', 'max:255'],
                'email'          => ['nullable', 'email', 'max:255'],
                'phone'          => ['sometimes', 'string'],
                'division'       => ['nullable', 'string'],
                'district'       => ['nullable', 'string'],
                'thana'          => ['nullable', 'string'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'email.email'           => 'Please provide a valid email address.',
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
