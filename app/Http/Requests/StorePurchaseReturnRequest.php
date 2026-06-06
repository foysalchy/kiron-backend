<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use App\Models\{Party, PurchaseDetail, PurchaseReturnDetail};
use App\Rules\UniqueProductIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePurchaseReturnRequest extends BaseCompanyRequest
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
                'purchase_id' => ['required', Rule::exists('purchases', 'id')
                    ->where('company_id', $companyId)],
                'supplier_id' => ['required', Rule::exists('parties', 'id')->where('type', Party::TYPE_SUPPLIER)
                    ->where('company_id', $companyId)],
                'return_date' => ['required', 'date'],
                'reason' => ['nullable', 'string'],
                'return_date' => ['required', 'date'],
                'reason' => ['nullable', 'string'],

                'items' => ['required', 'array', 'min:1',],
                'items.*.product_id' => ['required', 'exists:products,id'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0'],
                'items.*.discount' => ['nullable', 'numeric', 'min:0'],
                'items.*.tax_group_id' => ['nullable', 'integer'],
                'items.*.tax' => ['nullable', 'numeric', 'min:0'],
                'items.*.variation_id' => ['nullable'],

                'other_charges' => ['nullable', 'numeric', 'min:0'],
                'discount_on_all' => ['nullable', 'numeric', 'min:0'],
                'coupon_discount' => ['nullable', 'numeric', 'min:0'],
                'round_off' => ['nullable', 'numeric'],

                'payments' => ['nullable', 'array'],
                'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
                'payments.*.payment_method' => ['required_with:payments', 'string', 'in:cash,card,bank,mobile_banking,cheque'],
                'payments.*.reference_no' => ['nullable', 'string'],
                'payments.*.note' => ['nullable', 'string'],

                'note' => ['nullable', 'string'],
                'status' => ['nullable'],
            ]
        );
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('items') && $this->has('purchase_id')) {
                $this->validatePurchaseReturnItems($validator);
            }
        });
    }

    /**
     * Validate purchase return items
     */
    protected function validatePurchaseReturnItems($validator)
    {
        $purchaseId = $this->input('purchase_id');
        $items = $this->input('items', []);

        foreach ($items as $index => $item) {
            $productId = $item['product_id'] ?? null;
            $returnQuantity = $item['quantity'] ?? 0;

            if (!$productId) {
                continue;
            }

            // Check if product exists in purchase details with the given purchase_id
            $purchaseDetail = PurchaseDetail::where('purchase_id', $purchaseId)
                ->where('product_id', $productId)
                ->first();

            if (!$purchaseDetail) {
                $validator->errors()->add(
                    "items.{$index}.product_id",
                    "Product does not exist in the selected purchase."
                );
                continue;
            }


            if ($returnQuantity > $purchaseDetail->quantity) {
                $validator->errors()->add(
                    "items.{$index}.quantity",
                    " Purchased: {$purchaseDetail->quantity}, Request to returned: {$returnQuantity}."
                );
            }
        }
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'purchase_id.required' => 'Purchase is required',
                'supplier_id.required' => 'Supplier is required',
                'return_date.required' => 'Return date is required',

                'items.required' => 'At least one item is required',
                'items.*.product_id.required' => 'Product is required for each item',
                'items.*.quantity.required' => 'Quantity is required',
                'items.*.quantity.min' => 'Quantity must be at least 1',
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
