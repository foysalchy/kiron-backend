<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLeadNoteRequest extends BaseCompanyRequest
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
                'lead_id' => ['required', 'exists:leads,id'],
                'note'    => ['required', 'string'],
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
                'lead_id.required' => 'Lead reference is required.',
                'lead_id.exists'   => 'The selected lead is invalid.',
                'note.required'    => 'Note content cannot be empty.',
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
