<?php

namespace App\Http\Requests;

use App\Enums\Status;
use App\Http\Requests\UpdateBaseCompanyRequest;
use App\Rules\UniqueProductIds;
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

                'purchase_date' => ['sometimes', 'required', 'date'],
                'due_date' => ['nullable', 'date', 'after_or_equal:purchase_date'],

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
                'items.*.tax_group_id' => ['nullable'],
                'items.*.tax' => ['nullable', 'numeric', 'min:0'],
                'items.*.variation_id' => ['nullable'],
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
                'status' => [
                    'nullable',
                    Rule::in([
                        Status::Draft->value,      // 13
                        Status::Cancelled->value,  // 10
                        Status::Completed->value,  // 8
                    ]),
                ],
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
