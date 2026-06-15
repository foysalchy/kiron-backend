<?php
namespace App\Http\Requests\SuperAdmin;
 
use Illuminate\Foundation\Http\FormRequest;
 
class StoreSuperAdminSmsSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
 
    public function rules(): array
    {
        return [
            'company_ids'       => ['nullable', 'array'],
            'company_ids.*'     => ['integer', 'exists:companies,id'],
            'custom_numbers'    => ['nullable', 'array'],
            'custom_numbers.*'  => ['string', 'max:20'],
            'message'           => ['required', 'string', 'max:1600'],
        ];
    }
 
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $companyIds    = $this->input('company_ids', []);
            $customNumbers = $this->input('custom_numbers', []);
 
            if (empty($companyIds) && empty($customNumbers)) {
                $validator->errors()->add('company_ids', 'At least one recipient (seller or custom number) is required.');
            }
        });
    }
}