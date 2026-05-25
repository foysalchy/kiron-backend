<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class UpdateBaseCompanyRequest extends FormRequest
{
    /**
     * Common company_id validation rules
     */
    protected function companyRules(): array
    {
        $user = $this->user();

        return [
            'company_id' => $user->isSuperAdmin()
                ? ['nullable']
                : ['prohibited'],
        ];
    }

    /**
     * Common company_id validation messages
     */
    protected function companyMessages(): array
    {
        return [
            'company_id.nullable'   => 'Company ID is nullable for super admin.',
            'company_id.prohibited' => 'You are not allowed to specify company.',
        ];
    }
}
