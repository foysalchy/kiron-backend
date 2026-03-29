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
            'pricing_id'      => ['required', 'integer', 'exists:pricings,id'],
            'billing_cycle'   => ['required', 'in:monthly,yearly'],
            'payment_method'  => ['required', 'in:card,bank,mobile'],
        ];
    }
}