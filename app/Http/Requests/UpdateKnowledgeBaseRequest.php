<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateKnowledgeBaseRequest extends UpdateBaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id'); 

        return array_merge(
            $this->companyRules(),
            [
                'title'   => ['sometimes', 'required', 'string', 'max:255'],
                'slug'    => ['nullable', 'string', 'max:255', Rule::unique('knowledge_bases', 'slug')->ignore($id)],
                'content' => ['sometimes', 'required', 'string'],
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