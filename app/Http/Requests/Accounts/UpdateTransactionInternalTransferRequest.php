<?php

namespace App\Http\Requests\Accounts;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTransactionInternalTransferRequest extends UpdateBaseCompanyRequest
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
            'date'                          => ['sometimes', 'required', 'date'],
            'from_account_id'               => ['sometimes', 'required', 'exists:chart_of_accounts,id'],
            'description'                   => ['nullable', 'string'],
            'file'                          => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:5120'], //5mb
            'status'                        => ['sometimes', 'integer'],

            'items'                         => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.id'                    => ['sometimes', 'nullable', 'exists:transaction_transfer_details,id'],
            'items.*.chart_of_account_id'   => ['required_with:items', 'exists:chart_of_accounts,id'],
            'items.*.amount'                => ['required_with:items', 'numeric', 'min:0.01'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'from_account_id.exists'                    => 'The selected source account is invalid.',
            'items.required'                            => 'The transfer must contain at least one item.',
            'items.*.chart_of_account_id.required_with' => 'Every row must have a destination account.',
            'items.*.amount.min'                        => 'The row amount must be greater than zero.',
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
