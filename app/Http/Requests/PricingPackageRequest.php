<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PricingPackageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'mode' => 'required|in:regular,popular,recommended',
            'trial_days' => 'nullable|integer|min:0',

            'order_limit' => 'nullable|integer',
            'product_limit' => 'nullable|integer',
            'invoice_limit' => 'nullable|integer',
            'user_limit' => 'nullable|integer',

            'primary_domain' => 'nullable|string|max:255',
            'domain_limit' => 'nullable|integer',
            'extra_order_charge' => 'nullable',

            'features' => 'nullable|array',
            'features.*' => 'string|max:255',

            'multiple_input' => 'nullable|array',

            // Tiers Validation
            'tiers' => 'required|array|min:1',
            'tiers.*.billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'tiers.*.regular_price' => 'required|numeric|min:0',
            'tiers.*.discount_price' => 'nullable|numeric|min:0',
        ];
    }
}
