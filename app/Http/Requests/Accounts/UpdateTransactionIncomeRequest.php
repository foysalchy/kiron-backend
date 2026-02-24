<?php

namespace App\Http\Requests\Accounts;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTransactionIncomeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->companyRules(), [
            'income_to_id'    => ['sometimes', 'required', 'exists:chart_of_accounts,id'],
            'date'            => ['sometimes', 'required', 'date'],
            'description'     => ['nullable', 'string'],
            'file'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'max:5120'],
            'status'          => ['sometimes', 'integer'],

            // Validation for the Dynamic Rows (Income Categories)
            'items'                         => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.chart_of_account_id'   => ['required_with:items', 'exists:chart_of_accounts,id'],
            'items.*.amount'                => ['required_with:items', 'numeric', 'min:0'],
        ]);
    }public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'income_to_id.exists'         => 'The selected income destination is invalid.',
            'items.required'              => 'The income must contain at least one category row.',
            'items.*.chart_of_account_id.required_with' => 'Every row must have an income category.',
            'items.*.amount.numeric'      => 'The row amount must be a number.',
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
