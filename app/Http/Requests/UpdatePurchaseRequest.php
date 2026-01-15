<?php

namespace App\Http\Requests;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends UpdateBaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $user = $this->user();
        $companyId = $user->isSuperAdmin()
            ? $this->input('company_id')
            : $user->company_id;
        $purchaseId = $this->route('id');
        return array_merge(
            $this->companyRules(),
            [

                'warehouse_id' => [
                    'sometimes',
                    'required',
                    Rule::exists('warehouses', 'id')
                        ->where('company_id', $companyId),
                ],

                // Supplier (must be supplier + same company)
                'supplier_id' => [
                    'sometimes',
                    'required',
                    Rule::exists('parties', 'id')
                        ->where('company_id', $companyId)
                        ->where('type', 1),
                ],
                'reference_no' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('purchases', 'reference_no')->ignore($purchaseId)
                ],
                'purchase_date' => ['sometimes', 'required', 'date'],

                // Purchase Details
                'items' => ['sometimes', 'required', 'array', 'min:1'],
                'items.*.product_id' => [
                    'required',
                    Rule::exists('products', 'id')
                        ->where('company_id', $companyId),
                ],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
                'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
                'items.*.discount' => ['nullable', 'numeric', 'min:0'],
                'items.*.tax' => ['nullable', 'numeric', 'min:0'],

                // Totals
                'other_charges' => ['nullable', 'numeric', 'min:0'],
                'discount_on_all' => ['nullable', 'numeric', 'min:0'],
                'round_off' => ['nullable', 'numeric'],

                // Payment
                'payment_amount' => ['nullable', 'numeric', 'min:0'],
                'payment_type' => ['nullable', Rule::in(['cash', 'bank', 'card', 'cheque'])],
                'account' => ['nullable', 'string', 'max:255'],
                'payment_note' => ['nullable', 'string'],

                // Status
                'status' => ['nullable', Rule::in([0, 1, 2])],
                'note' => ['nullable', 'string'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'warehouse_id.required' => 'Warehouse is required',
                'supplier_id.required' => 'Supplier is required',
                'reference_no.required' => 'Reference number is required',
                'reference_no.unique' => 'Reference number already exists',
                'purchase_date.required' => 'Purchase date is required',
                'unit_cost.required' => 'Purchase date is required',
                'items.required' => 'At least one item is required',
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
