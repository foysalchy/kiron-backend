<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSiteSettingRequest extends UpdateBaseCompanyRequest
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
                'shop_name'         => ['sometimes', 'required', 'string', 'max:255'],
                'title'             => ['sometimes', 'required', 'string', 'max:255'],
                'description'       => ['nullable', 'string'],
                'logo'              => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
                'favicon'           => ['nullable', 'image', 'mimes:png,ico,svg', 'max:512'],
                'phone'             => ['sometimes', 'required', 'string', 'max:20'],
                'alternative_phone' => ['nullable', 'string', 'max:20'],
                'email'             => ['sometimes', 'required', 'email', 'max:255'],
                'corporate_address' => ['nullable', 'string'],
                'store_address'     => ['nullable', 'string'],
                'tags'              => ['nullable', 'string'],
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
                'shop_name.required' => 'Shop name cannot be empty.',
                'title.required'     => 'Site title is required.',
                'phone.required'     => 'Primary phone number is required.',
                'email.email'        => 'Invalid email format.',
                'logo.image'         => 'Logo must be an image file.',
                'favicon.max'        => 'Favicon must be less than 512KB.',
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
                'message' => 'Update validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
