<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLeadRequest extends BaseCompanyRequest
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
                'lead_source_id' => ['required', 'exists:lead_sources,id'],
                'lead_status_id' => ['required', 'exists:lead_statuses,id'],
                'full_name'      => ['required', 'string', 'max:255'],
                'email'          => ['nullable', 'email'],
                'phone'          => ['required', 'string'],
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
                'full_name.required'      => 'Customer name is required.',
                'phone.required'          => 'Contact number is required.',
                'division.required'       => 'Please select a division.',
            ]
        );
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
