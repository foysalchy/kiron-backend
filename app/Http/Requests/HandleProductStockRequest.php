<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\BaseCompanyRequest;
use App\Models\ProductStockLedger;
use Illuminate\Validation\Rule;

class HandleProductStockRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'warehouse_id'     => ['required', 'exists:warehouses,id'],
                'variation_id'     => ['nullable', 'exists:product_variations,id'],
                'bin_id'           => ['nullable', 'exists:bins,id'],
                'quantity'         => ['required', 'integer', 'not_in:0'],
                'batch_number'     => ['nullable', 'string', 'max:100'],
                'serial_numbers'   => ['nullable', 'array'],
                'serial_numbers.*' => ['string', 'max:100'],
                'transaction_type' =>  [
                    'required',
                    Rule::in(ProductStockLedger::TYPES),
                ],
                'reference_type'   => ['nullable', 'string', 'max:100'],
                'reference_id'     => ['nullable', 'integer'],
                'notes'            => ['nullable', 'string', 'max:500'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'amount.required' => 'Amount is required',
                'amount.numeric' => 'Amount must be a number'

            ]
        );
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
