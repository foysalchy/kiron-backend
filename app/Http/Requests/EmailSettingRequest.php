<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmailSettingRequest extends BaseCompanyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->companyRules(), [
            'host_name'     => ['sometimes', 'required', 'string', 'max:255'],
            'port_number'   => ['sometimes', 'required', 'string', 'max:10'],
            'auth_user'     => ['sometimes', 'required', 'string', 'max:255'],
            'auth_password' => ['sometimes', 'required', 'string'],
            'status'        => ['sometimes', 'required'],
        ]);
    }
    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'host_name.required'     => 'The SMTP host name is required',
                'port_number.required'   => 'The port number is required',
                'status.required' => 'Secure status must be enabled or disabled',
                'auth_user.required'     => 'The SMTP username/email is required',
                'auth_password.required' => 'The SMTP password is required',
            ]
        );
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
