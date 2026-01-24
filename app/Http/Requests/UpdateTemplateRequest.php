<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateTemplateRequest extends BaseCompanyRequest
{
    public function rules(): array
    {
        $templateId = $this->route('id') ?? $this->route('template');

        return array_merge(
            $this->companyRules(),
            [
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('templates', 'slug')->ignore($templateId),
                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
                ],
                'preview_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'status' => ['boolean'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required' => 'Template name is required.',
                'slug.unique' => 'This slug is already taken.',
                'slug.regex' => 'Slug must contain only lowercase letters, numbers, and hyphens.',
                'preview_image.image' => 'Preview image must be an image file.',
                'preview_image.max' => 'Preview image size cannot exceed 5MB.',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
