<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmailSettingRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->companyRules(), [
            'mail_mailer'       => ['sometimes', 'required', 'string', 'max:50'],
            'mail_host'         => ['sometimes', 'required', 'string', 'max:255'],
            'mail_port'         => ['sometimes', 'required', 'string', 'max:10'],
            'mail_username'     => ['sometimes', 'required', 'string', 'max:255'],
            'mail_password'     => ['sometimes', 'required', 'string'],
            'mail_encryption'   => ['sometimes', 'nullable', 'string', 'max:10'],
            'mail_from_address' => ['sometimes', 'required', 'email', 'max:255'],
            'mail_from_name'    => ['sometimes', 'required', 'string', 'max:255'],
            'is_verified'       => ['sometimes', 'boolean'],
        ]);
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'mail_host.required'         => 'The SMTP host name is required',
                'mail_port.required'         => 'The port number is required',
                'mail_username.required'     => 'The SMTP username/email is required',
                'mail_password.required'     => 'The SMTP password is required',
                'mail_from_address.required' => 'The from email address is required',
                'mail_from_address.email'    => 'The from address must be a valid email',
                'mail_from_name.required'    => 'The from name is required',
            ]
        );
    }
}
