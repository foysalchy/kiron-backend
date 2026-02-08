<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FirebaseSettingRequest extends BaseCompanyRequest
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
        return array_merge($this->companyRules(),[
            'email'               => ['sometimes', 'required', 'email', 'max:255'],
            'api_key'             => ['sometimes', 'required', 'string'],
            'auth_domain'         => ['sometimes', 'required', 'string'],
            'project_id'          => ['sometimes', 'required', 'string'],
            'storage_bucket'      => ['sometimes', 'required', 'string'],
            'messaging_sender_id' => ['sometimes', 'required', 'string'],
            'app_id'              => ['sometimes', 'required', 'string'],
            'measurement_id'      => ['nullable', 'string'],
            'google_auth'         => ['sometimes', 'integer'],
            'facebook_auth'       => ['sometimes', 'integer' ],
            'status'              => ['sometimes', 'integer'],
        ]);
    }
    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'email.required'               => 'Firebase contact email is required',
                'api_key.required'             => 'The Firebase API Key is required',
                'auth_domain.required'         => 'The Auth Domain is required',
                'project_id.required'          => 'The Project ID is required',
                'storage_bucket.required'      => 'Storage Bucket is required',
                'messaging_sender_id.required' => 'Messaging Sender ID is required',
                'app_id.required'              => 'The App ID is required',
            ]
        );
    }
    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
