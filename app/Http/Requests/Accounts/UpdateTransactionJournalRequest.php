<?php

namespace App\Http\Requests\Accounts;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTransactionJournalRequest extends UpdateBaseCompanyRequest
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
            'date'        => ['sometimes', 'required', 'date'],
            'description' => ['nullable', 'string'],
            'file'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'max:5120'],
            'status'      => ['sometimes', 'integer'],

            'items'                       => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.id'                  => ['sometimes', 'nullable', 'integer', 'exists:transaction_journal_accounts,id'],
            'items.*.chart_of_account_id' => ['required_with:items', 'exists:chart_of_accounts,id'],
            'items.*.debit'               => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'items.*.credit'              => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('items')) {
                $items = $this->input('items', []);

                foreach ($items as $index => $item) {
                    $debit  = (float) ($item['debit'] ?? 0);
                    $credit = (float) ($item['credit'] ?? 0);
                    $rowNum = $index + 1;

                    if ($debit > 0 && $credit > 0) {
                        $validator->errors()->add("items.$index", "Row $rowNum cannot have both Debit and Credit.");
                    }

                    if ($debit <= 0 && $credit <= 0) {
                        $validator->errors()->add("items.$index", "Row $rowNum must have a value in either Debit or Credit.");
                    }
                }

                $totalDebit  = collect($items)->sum('debit');
                $totalCredit = collect($items)->sum('credit');

                if (abs($totalDebit - $totalCredit) > 0.001) {
                    $validator->errors()->add('items', "Journal out of balance. Total Debit ($totalDebit) must equal Total Credit ($totalCredit).");
                }
            }
        });
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
