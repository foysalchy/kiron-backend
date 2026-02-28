<?php

namespace App\Http\Requests\Accounts;

use App\Enums\Status;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreRecurringJournalRequest extends BaseCompanyRequest
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
            'start_date'        => ['required', 'date', 'after_or_equal:today'],
            'amount'            => ['required', 'numeric', 'min:1'],
            'repeat_interval'   => ['required', 'integer', 'min:1'],
            'interval_type'     => ['required', Rule::in(['Day', 'Week', 'Month', 'Year'])],

            'from_account_id'   => ['required', 'exists:chart_of_accounts,id'],
            'to_account_id'     => ['required','exists:chart_of_accounts,id','different:from_account_id'],

            'description'       => ['nullable', 'string', 'max:1000'],
            'approval_status'   => ['required', Rule::in([Status::Draft->value, Status::Approved->value])],
            'operational_status'=> ['required', Rule::in([Status::Active->value, Status::Inactive->value])],
        ]);
    }
    /**
     * Custom messages for validation.
     */
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'from_account_id.required'  => 'Please select the account to debit from.',
            'to_account_id.required'    => 'Please select the account to credit to.',
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
