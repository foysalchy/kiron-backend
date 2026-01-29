<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordRequest extends UpdateBaseCompanyRequest
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
                'current_password' => ['required', 'string'],
                'new_password'     => ['required', 'string', 'min:8'],
                'confirm_password' => ['required', 'same:new_password'],
            ]
        );
    }
    protected function passedValidation()
    {
        $this->merge([
            'new_password' => Hash::make($this->new_password),
        ]);
    }
    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required',
            'new_password.required'     => 'New password is required',
            'new_password.min'          => 'Password must be at least 8 characters',
            'confirm_password.same'     => 'Confirmation password does not match',
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
