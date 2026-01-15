<?php

namespace App\Http\Requests;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePartyRequest extends UpdateBaseCompanyRequest
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
                'type' => ['sometimes', 'required', 'integer', Rule::in([1, 2])],
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['sometimes', 'required', 'string', 'max:20'],
                'alternative_phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string'],
                'balance' => ['nullable', 'numeric'],
                'profile' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
                'status' => ['integer'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'type.required' => 'Type is required',
                'type.in' => 'Type must be 1 (Supplier) or 2 (Customer)',
                'name.required' => 'Name is required',
                'email.email' => 'Please provide a valid email address',
                'phone.required' => 'Phone number is required',
                'profile.image' => 'Profile must be an image file',
                'profile.max' => 'Profile size cannot exceed 2MB',
                'balance.numeric' => 'Balance must be a number',
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
