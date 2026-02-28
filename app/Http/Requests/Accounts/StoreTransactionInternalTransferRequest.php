<?php

namespace App\Http\Requests\Accounts;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTransactionInternalTransferRequest extends BaseCompanyRequest
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
            'from_account_id'               => ['required', 'exists:chart_of_accounts,id'],
            'date'                          => ['required', 'date'],
            'description'                   => ['required', 'string'],
            'file'                          => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'max:5120'], //5mb
            'status'                        => ['nullable', 'integer'],

            'items'                         => ['required', 'array', 'min:1'],
            'items.*.chart_of_account_id'   => ['required', 'exists:chart_of_accounts,id'],
            'items.*.amount'                => ['required', 'numeric', 'min:0.01'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'from_account_id.required'              => 'Please select the source account.',
            'items.required'                        => 'You must add at least one transfer item.',
            'items.*.chart_of_account_id.required'  => 'Please select a destination account for all rows.',
            'items.*.amount.required'               => 'The amount field is required for all rows.',
            'items.*.amount.min'                    => 'The amount must be greater than zero.',
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
