<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNoticeRequest extends BaseCompanyRequest
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
                'title'       => ['required', 'string', 'max:255'],
                'slug'        => ['nullable', 'string', 'max:255', 'unique:notices,slug'],
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
