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
            'company_id' => [
                $user->isSuperAdmin() ? 'sometimes|required' : 'prohibited',
                'exists:companies,id',
            ],
        ];
    }

    /**
     * Common company_id validation messages
     */
    protected function companyMessages(): array
    {
        return [
            'company_id.required'   => 'Company ID is required for super admin.',
            'company_id.prohibited' => 'You are not allowed to specify company.',
            'company_id.exists'     => 'Selected company does not exist.',
        ];
    }
}
