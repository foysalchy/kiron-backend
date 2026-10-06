<?php

namespace App\Http\Requests\Saas;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMasterDemoRequest extends FormRequest
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
            'title'  => 'sometimes|required|string|max:255',
            'image'  => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link'   => 'sometimes|nullable|url',
            'type'   => 'sometimes|required',
            'status' => 'sometimes|required|integer',
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
            'title.required'  => 'The demo title cannot be empty.',
            'image.image'     => 'The uploaded file must be a valid image.',
            'image.mimes'     => 'Supported image formats are: jpeg, png, jpg, webp.',
            'image.max'       => 'The image size must not exceed 2MB.',
            'link.url'        => 'The link must be a valid URL format.',
            'status.required' => 'The status field is required.',
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
