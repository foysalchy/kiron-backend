<?php

namespace App\Http\Requests\CompanyRegistration;

use Illuminate\Foundation\Http\FormRequest;

class ResendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_id' => ['required', 'integer', 'exists:companies,id'],
            'type'            => ['required', 'in:company,user'],
        ];
    }
}
