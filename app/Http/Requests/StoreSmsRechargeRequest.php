<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSmsRechargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    return [
        'sms_package_id'   => ['required', 'exists:sms_packages,id'],
        'payment_method'   => ['required', 'in:cash,bank,bkash,nagad'],
        'transaction_id'   => ['nullable', 'string', 'max:255'],
        'account_number'   => ['nullable', 'string', 'max:255'],
        'note'             => ['nullable', 'string'],
    ];
}



    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
