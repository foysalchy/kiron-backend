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

    protected function getCompanyId()
    {
        return $this->input('company_id') ?? $this->user()?->company_id;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        $companyId = $this->getCompanyId();

        return array_merge(
            $this->companyRules(),
            [
                'title'   => ['sometimes', 'required', 'string', 'max:255'],
                'slug'    => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('knowledge_bases', 'slug')->ignore($id)->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    }),
                ],
                'content' => ['sometimes', 'required', 'string'],
                'category' => ['sometimes', 'required', 'string'],
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
