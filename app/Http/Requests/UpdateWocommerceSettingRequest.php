<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateWocommerceSettingRequest extends UpdateBaseCompanyRequest
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
                'domain_url'      => ['sometimes', 'url', 'max:255'],
                'consumer_key'    => ['sometimes', 'string', 'max:255'],
                'consumer_secret' => ['sometimes', 'string', 'max:255'],
                'status'          => ['nullable', 'integer'],
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
                'domain_url.url' => 'Please provide a valid URL format.',
            ]
        );
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator$validator)
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
