<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateNoticeRequest extends UpdateBaseCompanyRequest
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
                'title'       => ['sometimes', 'required', 'string', 'max:255'],
                'slug'        => ['nullable', 'string', 'max:255', Rule::unique('notices', 'slug')->ignore($id)],
                'description' => ['nullable', 'string'],
                'image'       => ['nullable'], // string path if keeping existing, or image file if updating
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
