<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use App\Rules\SlugRule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreBrandRequest extends BaseCompanyRequest
{

    protected function getCompanyId()
    {
        return $this->input('company_id') ?? $this->user()->company_id;
    }
    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'name'   => ['required', 'string', 'max:255'],
                'logo'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
                'slug' => SlugRule::make(
                    'brands',
                    null,
                    $this->getCompanyId()
                ),
                'description' => ['nullable', 'string'],
                'meta_title' => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string', 'max:255'],
                'meta_keywords' => ['nullable','string'],
                'status' => ['boolean'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required' => 'Brand name is required.',
                'logo.image'    => 'Logo must be an image file.',
                'logo.max'      => 'Logo size cannot exceed 2MB.',
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
