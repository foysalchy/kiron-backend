<?php

namespace App\Http\Requests\Accounts;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTransactionJournalRequest extends BaseCompanyRequest
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
            'date'        => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'file'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:5120'],
            'status'      => ['nullable', 'integer'],

            // Journal items validation
            'items'                       => ['required', 'array', 'min:2'],
            'items.*.chart_of_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'items.*.debit'               => ['required_without:items.*.credit', 'numeric', 'min:0'],
            'items.*.credit'              => ['required_without:items.*.debit', 'numeric', 'min:0'],
        ]);
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($items as $index => $item) {
                $debit = (float) ($item['debit'] ?? 0);
                $credit = (float) ($item['credit'] ?? 0);
                $rowNum = $index + 1;

                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add("items.$index", "Row $rowNum cannot have both Debit and Credit.");
                }

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            if ($totalDebit > 0 && $totalCredit > 0) {
                if (abs($totalDebit - $totalCredit) > 0.001) {
                    $validator->errors()->add('items', "The journal is out of balance. Total Debit ($totalDebit) must equal Total Credit ($totalCredit).");
                }
            }
        });
    }

    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'items.*.debit.required_without'  => 'Either Debit or Credit is mandatory for this item.',
            'items.*.credit.required_without' => 'Either Debit or Credit is mandatory for this item.',
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
