<?php
namespace App\Http\Requests\SuperAdmin;
 
use Illuminate\Foundation\Http\FormRequest;
 
class StoreSuperAdminEmailSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
 
    public function rules(): array
    {
        return [
            'company_ids'            => ['nullable', 'array'],
            'company_ids.*'          => ['integer', 'exists:companies,id'],
            'custom_emails'          => ['nullable', 'array'],
            'custom_emails.*'        => ['email'],
            'subject'                => ['required', 'string', 'max:255'],
            'body'                   => ['required', 'string'],
        ];
    }
 
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $companyIds   = $this->input('company_ids', []);
            $customEmails = $this->input('custom_emails', []);
 
            if (empty($companyIds) && empty($customEmails)) {
                $validator->errors()->add('company_ids', 'At least one recipient (seller or custom email) is required.');
            }
        });
    }
}