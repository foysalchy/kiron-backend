<?php

namespace App\Http\Requests\Accounts;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTransactionExpenseRequest extends BaseCompanyRequest
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
            'expense_from_id' => ['required', 'exists:chart_of_accounts,id'],
            'date'            => ['required', 'date'],
            'description'     => ['required', 'string'],
            'file'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'max:5120'], //5mb
            'status'          => ['nullable', 'integer'],
            'items'                         => ['required', 'array', 'min:1'],
            'items.*.chart_of_account_id'   => ['required', 'exists:chart_of_accounts,id'],
            'items.*.amount'                => ['required', 'numeric', 'min:1'],
        ]);
    }
    /**
     * Custom messages for validation.
     */
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'expense_from_id.required'    => 'Please select the account where the money is coming from.',
            'items.required'              => 'You must add at least one expense category row.',
            'items.*.chart_of_account_id.required' => 'Please select an expense category.',
            'items.*.amount.required'     => 'The amount field is required for all rows.',
            'items.*.amount.numeric'      => 'The amount must be a number.',
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
