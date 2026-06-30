<?php

namespace App\Http\Requests\Saas;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMasterBrandRequest extends FormRequest
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
        return [
            'name' => 'sometimes|required|string|max:255',
            'logo' => 'sometimes|nullable|image|max:2048',
            'link' => 'sometimes|nullable|url',
            'meta_title' => 'nullable| string',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable',
        ];
    }
    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The brand name cannot be empty.',
            'logo.image'    => 'The logo must be a valid image file.',
            'logo.mimes'    => 'Supported image formats are: jpeg, png, jpg, webp.',
            'logo.max'      => 'The logo size must not exceed 2MB.',
            'link.url'      => 'The link must be a valid URL format.',
        ];
    }

    /**
     * Handle a failed validation attempt for API response.
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
