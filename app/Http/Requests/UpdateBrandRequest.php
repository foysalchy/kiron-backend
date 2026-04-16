<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\UpdateBaseCompanyRequest;

class UpdateBrandRequest extends UpdateBaseCompanyRequest
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

                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
                'meta_description' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
                'status' => ['boolean'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyRules(),
            [

                'name.required' => 'Brand name is required',
                'logo.image' => 'Logo must be an image file',
                'logo.max' => 'Logo size cannot exceed 2MB',
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
