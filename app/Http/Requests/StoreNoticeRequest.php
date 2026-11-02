<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreNoticeRequest extends BaseCompanyRequest
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
        $companyId = $this->getCompanyId();

        return array_merge(
            $this->companyRules(),
            [
                'title'       => ['required', 'string', 'max:255'],
                'slug'        => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('notices', 'slug')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    }),
                ],
                'description' => ['nullable', 'string'],
                'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'title.required' => 'Title is required.',
                'slug.unique'    => 'This slug is already taken.',
                'image.image'    => 'The file must be an image.',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
