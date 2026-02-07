<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreWocommerceSettingRequest extends BaseCompanyRequest
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
                'domain_url'      => ['required', 'url', ''],
                'consumer_key'    => ['required', 'string', ''],
                'consumer_secret' => ['required', 'string', ''],
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
                'domain_url.required'      => 'WooCommerce domain URL is required.',
                'domain_url.url'           => 'Please provide a valid URL (e.g., https://yourstore.com).',
                'consumer_key.required'    => 'Consumer Key is mandatory for API integration.',
                'consumer_secret.required' => 'Consumer Secret is mandatory for API integration.',
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
