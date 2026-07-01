<?php

namespace App\Http\Requests\Saas;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMasterFeatureRequest extends FormRequest
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
            'title'       => 'required|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'image'       => 'nullable|image|max:2048',
            'icon'        => 'nullable',
            'slug' => 'required|string',
            'description' => 'nullable|string',
            'meta_title' => 'nullable| string',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable',
            'placement'   => 'required|in:1,2', // 1=feature, 2=benefit
            'status'      => 'nullable|integer',
        ];
    }
    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required'     => 'The feature title is required.',
            'title.string'       => 'The title must be valid text.',
            'image.image'        => 'The uploaded file must be an image.',
            'image.mimes'        => 'Supported image formats are: jpeg, png, jpg, webp.',
            'image.max'          => 'The image size cannot exceed 2MB.',
            'placement.required' => 'Placement is required (1 for Feature, 2 for Benefit).',
            'placement.in'       => 'Invalid placement selected. Please choose 1 (Feature) or 2 (Benefit).',
            'description.max'    => 'The description cannot be longer than 1000 characters.',
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
