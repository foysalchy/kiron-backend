<?php

namespace App\Http\Requests;

use App\Http\Requests\UpdateBaseCompanyRequest;
use App\Models\{Party, PurchaseDetail, PurchaseReturnDetail};
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePurchaseReturnRequest extends UpdateBaseCompanyRequest
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
                'purchase_id' => ['sometimes', Rule::exists('purchases', 'id')
                    ->where('company_id', $companyId)],
                'supplier_id' => ['sometimes', Rule::exists('parties', 'id')->where('type', Party::TYPE_SUPPLIER)
                    ->where('company_id', $companyId)],
                'return_date' => ['sometimes', 'date'],

                'items' => ['sometimes', 'array', 'min:1'],
                'items.*.product_id' => ['required_with:items', 'exists:products,id'],
                'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],

                'note' => ['nullable', 'string'],
                'status' => ['sometimes', 'integer', 'in:0,1,2'],
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
                'purchase_id.exists' => 'Invalid purchase',
                'supplier_id.exists' => 'Invalid supplier',
                'return_date.date' => 'Return date must be a valid date',

                'items.min' => 'At least one item is required',
                'items.*.product_id.required_with' => 'Product is required for each item',
                'items.*.quantity.required_with' => 'Quantity is required',
                'items.*.quantity.min' => 'Quantity must be at least 1',

                'status.in' => 'Invalid status value',
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
