<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnifiedSellerRegistrationRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            // Account & Company Identity
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|unique:users,email',
            'phone'               => 'required|string|max:50',
            'business_type'       => 'required|integer',
            'password'            => 'required|string|min:8|confirmed',

            // Pricing Package & Billing Cycle
            'pricing_package_id'  => 'required|exists:pricing_packages,id',
            'billing_cycle'       => 'required|in:monthly,quarterly,yearly',
            'payment_method'      => 'required|string|max:255',

            // Conditional Manual Payment Fields (Required only if payment_method is manual or bank)
            'account_holder_name' => 'required_if:payment_method,manual,bank|nullable|string|max:255',
            'number'              => 'required_if:payment_method,manual,bank|nullable|string|max:50',
            'transaction_id'      => 'required_if:payment_method,manual,bank|nullable|string|max:255',
            'document'            => 'required_if:payment_method,manual,bank|nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',

            // Core Store Configuration
            'sub_domain'          => 'required|string|max:255|',
            'lang'                => 'required|string|in:en,bn,ar',
            'currency'            => 'required|string|max:10',
            'manage_warehouse'    => 'required|in:0,1',
        ];
    }
}