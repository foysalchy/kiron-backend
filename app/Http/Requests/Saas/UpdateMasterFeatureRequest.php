<?php

namespace App\Http\Requests\Saas;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMasterFeatureRequest extends FormRequest
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
            'title'       => 'sometimes|required|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'image'       => 'nullable|image|max:2048',
            'icon'        => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'meta_title' => 'nullable| string',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable',
            'placement'   => 'sometimes|required|in:1,2',
            'status'      => 'sometimes|required|integer',
        ];
    }
    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required'     => 'The title cannot be empty if provided.',
            'image.image'        => 'The file must be a valid image.',
            'image.mimes'        => 'Supported image formats: jpeg, png, jpg, webp.',
            'image.max'          => 'The image size must not exceed 2MB.',
            'placement.required' => 'Placement selection is required.',
            'placement.in'       => 'Invalid placement. Choose 1 (Feature) or 2 (Benefit).',
            'status.required'    => 'The status field is required.',
            'status.integer'     => 'The status must be a numeric value.',
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
