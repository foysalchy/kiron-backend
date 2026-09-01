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
            'reference_number' => ['nullable', 'string', 'max:100'],
            'voucher_type'     => ['nullable', 'string', 'max:50'],
            'voucher_no'       => ['nullable', 'string', 'max:100'],
            'source_type'      => ['nullable', 'string', 'max:50'],
            'source_id'        => ['nullable', 'integer'],
            'party_id'         => ['nullable', 'exists:parties,id'],
            'date'             => ['sometimes', 'required', 'date'],
            'description'      => ['nullable', 'string'],
            'narration'        => ['nullable', 'string'],
            'file'             => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp,doc,docx,xls,xlsx', 'max:5120'],
            'status'           => ['sometimes', 'integer'],

            'items'                       => ['sometimes', 'array', 'min:1'],
            'items.*.id'                  => ['sometimes', 'nullable', 'integer', 'exists:transaction_journal_accounts,id'],
            'items.*.chart_of_account_id' => ['required_with:items', 'exists:chart_of_accounts,id'],
            'items.*.debit'               => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'items.*.credit'              => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->has('items')) {
                return;
            }

            $items       = $this->input('items', []);
            $totalDebit  = 0;
            $totalCredit = 0;

            foreach ($items as $index => $item) {
                $debit  = (float) ($item['debit'] ?? 0);
                $credit = (float) ($item['credit'] ?? 0);
                $rowNum = $index + 1;

                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add("items.$index", "Row $rowNum cannot have both Debit and Credit.");
                }

                if ($debit <= 0 && $credit <= 0) {
                    $validator->errors()->add("items.$index", "Row $rowNum must have either a Debit or a Credit value.");
                }

                $totalDebit  += $debit;
                $totalCredit += $credit;
            }

            if (abs($totalDebit - $totalCredit) > 0.001) {
                $validator->errors()->add('items', "Journal is unbalanced. Total Debit ($totalDebit) must equal Total Credit ($totalCredit).");
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
