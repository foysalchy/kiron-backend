<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateBlogRequest extends UpdateBaseCompanyRequest
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
        $blogId = $this->route('id') ?? $this->route('blog');

        return array_merge(
            $this->companyRules(),
            [
                'title'            => ['sometimes', 'required', 'string', 'max:255'],
                'slug'             => ['sometimes', 'required', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($blogId)],
                'short'            => ['sometimes', 'required', 'string'],
                'body'             => ['nullable', 'string'],
                'body_2'           => ['nullable', 'string'],
                'body_3'           => ['nullable', 'string'],

                // Images (JSON array of files)
                'images'   => ['sometimes', 'nullable', 'array'],
                'existing_images'   => ['sometimes', 'nullable', 'array'],
                'images.*' => ['image', 'mimes:jpeg,png,jpg', 'max:2048'],

                // SEO Metadata
                'meta_title'       => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string'],
                'meta_keywords'    => ['nullable', 'array'],
                'status'           => ['sometimes', 'integer', 'in:0,1'],
            ]
        );
    }
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'title.required' => 'The blog title is required',
                'slug.required'  => 'The blog slug is required',
                'slug.unique'    => 'This slug is already taken by another blog',
                'short.required' => 'The short description is required',
                'images.*.image' => 'Each file in the images array must be an image',
                'images.*.max'   => 'Individual images cannot exceed 2MB',
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
