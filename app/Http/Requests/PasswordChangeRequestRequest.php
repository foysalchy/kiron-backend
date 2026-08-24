<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', 'in:email,sms'],
        ];
    }

    public function messages(): array
    {
        return [
            'method.required' => 'Please select a method (email or SMS)',
            'method.in'        => 'Method must be either email or sms',
        ];
    }
}