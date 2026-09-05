<?php

namespace App\Http\Requests\CompanyRegistration;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration_id' => ['required', 'integer', 'exists:companies,id'],
            'pricing_package_id'      => ['required', 'integer', 'exists:pricing_packages,id'],
            'billing_cycle'   => ['required'],
            'payment_method'  => 'required|in:card,bank,mobile,manual',
            'transaction_id'      => 'required_if:payment_method,manual,bank|nullable|string|max:255',
            'number'              => 'required_if:payment_method,manual,bank|nullable|string|max:255',
            'account_holder_name' => 'required_if:payment_method,manual,bank|nullable|string|max:255',
            'document'            => 'required_if:payment_method,manual,bank|nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'referral_code'       => 'nullable|string|max:50',
        ];
    }
}
