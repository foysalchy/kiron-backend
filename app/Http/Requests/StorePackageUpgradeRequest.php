<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageUpgradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pricing_package_id'  => 'required|exists:pricing_packages,id',
            'billing_cycle'       => 'required|in:monthly,quarterly,yearly',
            'account_holder_name' => 'nullable|string|max:255',
            'payment_method'      => 'required|string|max:255',
            'number'              => 'nullable|string|max:50',
            'transaction_id'      => 'nullable|string|max:255|unique:updgrade_package_requests,transaction_id',
            'document'            => 'required|image|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ];
    }
}