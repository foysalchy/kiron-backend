<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreBlogRequest extends BaseCompanyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function getCompanyId()
    {
        return $this->input('company_id') ?? $this->user()?->company_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->getCompanyId();

        return array_merge(
            $this->companyRules(),
            [
                'title'            => ['required', 'string', 'max:255'],
                'slug'             => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('blogs', 'slug')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    }),
                ],
                'short'            => ['required', 'string'],
                'body'             => ['nullable', 'string'],
                'body_2'           => ['nullable', 'string'],
                'body_3'           => ['nullable', 'string'],

                // Multi-image upload for the JSON column
                'images'           => ['nullable', 'array'],
                'images.*'         => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],

                // SEO Metadata
                 'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string', 'max:255'],
                'meta_keywords' => ['nullable', 'string'],

                'status'           => ['nullable', 'integer', 'in:0,1'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'title.required' => 'The blog title is required.',
                'slug.required'  => 'The blog slug is required.',
                'slug.unique'    => 'This slug is already in use.',
                'short.required' => 'A short description is required.',
                'images.*.image' => 'Each file must be an image.',
                'images.*.max'   => 'Individual images cannot exceed 2MB.',
            ]
        );
    }

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
