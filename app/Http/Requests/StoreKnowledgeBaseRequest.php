<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreKnowledgeBaseRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'title'   => ['required', 'string', 'max:255'],
                'slug'    => ['nullable', 'string', 'max:255', 'unique:knowledge_bases,slug'],
                'content' => ['required', 'string'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'title.required'   => 'Title is required.',
                'content.required' => 'Content is required.',
                'slug.unique'      => 'This slug is already taken.',
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