<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required',
            'company_id.exists' => 'Selected company does not exist',
            'name.required' => 'Brand name is required',
            'logo.image' => 'Logo must be an image file',
            'logo.max' => 'Logo size cannot exceed 2MB',
        ];
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