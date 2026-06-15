<?php

namespace App\Http\Requests\Accounts;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTransactionExpenseRequest extends UpdateBaseCompanyRequest
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
            'expense_from_id' => ['sometimes', 'required', 'exists:chart_of_accounts,id'],
            'date'            => ['sometimes', 'required', 'date'],
            'description'     => ['nullable', 'string'],
            'file'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:5120'], //5mb
            'status'          => ['sometimes', 'integer'],

            // Validation for the Dynamic Rows (Categories)
            'items'                         => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.chart_of_account_id'   => ['required_with:items', 'exists:chart_of_accounts,id'],
            'items.*.amount'                => ['required_with:items', 'numeric', 'min:0'],
        ]);
    }
    /**
     * Custom messages for validation.
     */
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'expense_from_id.exists'      => 'The selected payment source is invalid.',
            'items.required'              => 'The expense must contain at least one category row.',
            'items.*.chart_of_account_id.required_with' => 'Every row must have an expense category.',
            'items.*.amount.numeric'      => 'The row amount must be a number.',
        ]);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
