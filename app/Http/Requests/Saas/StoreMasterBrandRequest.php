<?php

namespace App\Http\Requests\Saas;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMasterBrandRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'link' => 'nullable|url',
            'meta_title' => 'nullable| string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
        ];
    }
    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The brand name is required.',
            'name.string'   => 'The brand name must be a valid string.',
            'logo.image'    => 'The logo must be an image file.',
            'logo.mimes'    => 'The logo must be a file of type: jpeg, png, jpg, webp.',
            'logo.max'      => 'The logo size should not exceed 2MB.',
            'link.url'      => 'Please provide a valid URL for the brand link.',
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
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
