<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest // Or BaseCompanyRequest if applicable
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name'              => ['sometimes', 'required', 'string', 'max:255'],
            'email'             => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'phone'             => ['sometimes', 'required', 'string', 'max:20'],
            'alternative_phone' => ['nullable', 'string', 'max:20'],
            'profile'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'password'          => ['nullable', 'string', 'min:6'], // Nullable during update
            'role'              => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Name is required.',
            'email.required'    => 'Email address is required.',
            'email.unique'      => 'This email is already in use.',
            'phone.required'    => 'Phone number is required.',
        ];
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
