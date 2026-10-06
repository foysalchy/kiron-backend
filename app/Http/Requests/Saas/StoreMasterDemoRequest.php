<?php

namespace App\Http\Requests\Saas;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMasterDemoRequest extends FormRequest
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
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'  => 'required|string|max:255',
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link'   => 'nullable|url',
            'type'  => 'required',
            'slug' => 'required|string',
            'status' => 'nullable|integer',
            'description' => 'nullable|string',
            'meta_title' => 'nullable| string|max:255',
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
            'title.required' => 'The demo title is required.',
            'title.string'   => 'The title must be a valid string.',
            'image.image'    => 'The file must be an image (jpg, png, etc.).',
            'image.max'      => 'The image size cannot exceed 2MB.',
            'link.url'       => 'Please provide a valid URL (e.g., https://demo.example.com).',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * Returns a JSON response instead of a redirect.
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
