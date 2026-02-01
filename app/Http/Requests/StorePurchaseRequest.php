<?php

namespace App\Http\Requests;

use App\Enums\Status;
use App\Http\Requests\BaseCompanyRequest;
use App\Models\Party;
use App\Rules\UniqueProductIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends BaseCompanyRequest
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

        return array_merge(
            $this->companyRules(),
            [
                //check warehouse
                'warehouse_id' => [
                    'required',
                    Rule::exists('warehouses', 'id')
                        ->where('company_id', $companyId),
                ],

                //check supplier
                'supplier_id' => [
                    'required',
                    Rule::exists('parties', 'id')
                        ->where('company_id', $companyId)
                        ->where('type', Party::TYPE_SUPPLIER),
                ],
                'purchase_date' => ['required', 'date'],

                // Purchase Details
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => [
                    'required',
                    Rule::exists('products', 'id')
                        ->where('company_id', $companyId),
                ],
                'items.*.quantity' => ['required', 'integer', 'min:1', new UniqueProductIds],
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
                'warehouse_id.exists' => 'Selected warehouse does not belong to your company.',
                'supplier_id.required' => 'Supplier is required',
                'supplier_id.exists'  => 'Selected supplier is invalid or does not belong to your company.',
                'purchase_date.required' => 'Purchase date is required',
                'items.required' => 'At least one item is required',
                'items.*.product_id.required' => 'Product is required for each item',
                'items.*.product_id.exists' =>
                'Selected product does not belong to the selected company.',
                'items.*.quantity.required' => 'Quantity is required',
                'items.*.quantity.min' => 'Quantity must be at least 1',
                'items.*.purchase_price.required' => 'Purchase price is required',
                'items.*.unit_cost.required' => 'Unit Cost  is required',
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
