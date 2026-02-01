<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNoteTemplateRequest extends BaseCompanyRequest
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
                'title'   => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'status'  => ['nullable', 'integer'],
            ]
        );
    }
    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'title.required'   => 'Template title is mandatory.',
                'content.required' => 'Template content cannot be empty.',
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
