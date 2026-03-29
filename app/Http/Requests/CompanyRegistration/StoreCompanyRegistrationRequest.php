<?php

namespace App\Http\Requests\CompanyRegistration;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public registration – no auth guard needed
    }

    public function rules(): array
    {
        return [
            // ── Company ──────────────────────────────────────
            'company'                => ['required', 'array'],
            'company.name'           => ['required', 'string', 'max:255'],
            'company.email'          => ['required', 'email', 'max:255', 'unique:companies,email'],
            'company.phone'          => ['required', 'string', 'max:20'],
            'company.business_type'  => ['nullable', 'integer', 'in:1,2,3,4,5'],

            // ── Admin User ────────────────────────────────────
            'user'                         => ['required', 'array'],
            'user.name'                    => ['required', 'string', 'max:255'],
            'user.email'                   => ['required', 'email', 'max:255', 'unique:users,email'],
            'user.phone'                   => ['required', 'string', 'max:20'],
            'user.password'                => ['required', 'string', 'min:8', 'confirmed'],
            'user.password_confirmation'   => ['required', 'string'],

            // ── Subscription ──────────────────────────────────
            'pricing_id'     => ['required', 'integer', 'exists:pricings,id'],
            'billing_cycle'  => ['required', 'in:monthly,yearly'],
            'payment_method' => ['required', 'in:card,bank,mobile'],
        ];
    }

    public function messages(): array
    {
        return [
            'company.email.unique' => 'A company with this email already exists.',
            'user.email.unique'    => 'A user with this email already exists.',
            'user.password.confirmed' => 'Passwords do not match.',
            'pricing_id.exists'    => 'The selected pricing plan is invalid.',
        ];
    }
}