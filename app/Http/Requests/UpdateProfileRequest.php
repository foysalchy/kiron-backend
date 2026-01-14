<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return array_merge(
            $this->companyRules(),
            [
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($userId)],
                'phone' => ['sometimes', 'required', 'string', 'max:20'],
                'alternative_phone' => ['nullable', 'string', 'max:20'],
                'profile' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required' => 'Name is required',
                'email.required' => 'Email is required',
                'email.email' => 'Please provide a valid email address',
                'email.unique' => 'This email is already registered',
                'phone.required' => 'Phone number is required',
                'profile.image' => 'Profile must be an image file',
                'profile.max' => 'Profile size cannot exceed 2MB',
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
