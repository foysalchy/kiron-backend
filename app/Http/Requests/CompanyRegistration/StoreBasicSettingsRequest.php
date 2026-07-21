<?php

namespace App\Http\Requests\CompanyRegistration;

use Illuminate\Foundation\Http\FormRequest;

class StoreBasicSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_id' => ['required', 'integer', 'exists:companies,id'],
            'sub_domain' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                'unique:domain_setups,sub_domain'
            ],
            'lang'   => ['required'],
            'currency'  => ['required'],
            'currency_position'  => ['required'],
            'manage_warehouse'  => ['required'],
        ];
    }
}
