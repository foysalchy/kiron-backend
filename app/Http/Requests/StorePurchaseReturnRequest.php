<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use App\Models\{PurchaseDetail, PurchaseReturnDetail};
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePurchaseReturnRequest extends BaseCompanyRequest
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
                'purchase_id' => ['required', 'exists:purchases,id'],
                'supplier_id' => ['required', 'exists:parties,id'],
                'return_date' => ['required', 'date'],

                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'exists:products,id'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],

                'note' => ['nullable', 'string'],
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

      
            $availableQuantity = $purchaseDetail->quantity;

            if ($returnQuantity > $availableQuantity) {
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
