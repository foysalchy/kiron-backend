<?php

namespace App\Http\Requests\Accounts;

use App\Enums\Status;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateRecurringJournalRequest extends UpdateBaseCompanyRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->companyRules(), [
            'amount'            => ['sometimes', 'required', 'numeric', 'min:1'],
            'repeat_interval'   => ['sometimes', 'required', 'integer', 'min:1'],
            'interval_type'     => ['sometimes', 'required', Rule::in(['Day', 'Week', 'Month', 'Year'])],
            'from_account_id'   => ['sometimes', 'required', 'exists:chart_of_accounts,id'],
            'to_account_id'     => ['sometimes','required','exists:chart_of_accounts,id','different:from_account_id' ],
            'description'       => ['sometimes', 'string'],
            'approval_status'   => ['sometimes', 'required', Rule::in([Status::Draft->value, Status::Approved->value])],
            'operational_status'=> ['sometimes', 'required', Rule::in([Status::Active->value, Status::Inactive->value])],
        ]);
    }
    /**
     * Custom messages for validation.
     */
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'to_account_id.different'   => 'The debit and credit accounts must be different.',
            'repeat_interval.min'       => 'The repeat interval must be at least 1.',
            'interval_type.in'          => 'Please select a valid interval type (Day, Week, Month, or Year).',
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
