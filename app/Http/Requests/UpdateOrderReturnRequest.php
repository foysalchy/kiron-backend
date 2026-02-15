<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\UpdateBaseCompanyRequest;
use App\Models\OrderDetail;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateOrderReturnRequest extends UpdateBaseCompanyRequest
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
                'order_id' => ['required',   Rule::exists('orders', 'id')
                    ->where('company_id', $companyId),],
                'return_date' => ['sometimes', 'date'],
                'reason' => ['nullable', 'string'],

                'items' => ['sometimes', 'array', 'min:1'],
                'items.*.product_id' => ['required_with:items', 'exists:products,id'],
                'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
                'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
                'items.*.discount' => ['nullable', 'numeric', 'min:0'],
                'items.*.tax' => ['nullable', 'numeric', 'min:0'],
                'items.*.variation_id' => ['nullable'],


                'other_charges' => ['nullable', 'numeric', 'min:0'],
                'discount_on_all' => ['nullable', 'numeric', 'min:0'],
                'coupon_discount' => ['nullable', 'numeric', 'min:0'],
                'round_off' => ['nullable', 'numeric'],

                'note' => ['nullable', 'string'],
                'status' => ['sometimes', 'integer'],
            ]
        );
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('items') && $this->has('order_id')) {
                $this->validateReturnItems($validator);
            }
        });
    }

    /**
     * Validate return items against original order
     */
    protected function validateReturnItems($validator)
    {
        $orderId = $this->input('order_id');
        $items = $this->input('items', []);

        foreach ($items as $index => $item) {
            $productId = $item['product_id'] ?? null;
            $returnQuantity = $item['quantity'] ?? 0;

            if (!$productId) {
                continue;
            }

            // Check if product exists in order details
            $orderDetail = OrderDetail::where('order_id', $orderId)
                ->where('product_id', $productId)
                ->first();

            if (!$orderDetail) {
                $validator->errors()->add(
                    "items.{$index}.product_id",
                    "Product does not exist in the selected order."
                );
                continue;
            }




            if ($returnQuantity > $orderDetail->quantity) {
                $validator->errors()->add(
                    "items.{$index}.quantity",
                    " Order : {$orderDetail->quantity}, Request to returned: {$returnQuantity}."
                );
            }
        }
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'order_id.required' => 'Order is required',
                'order_id.exists' => 'Selected order does not belong to your company.',
                'return_date.required' => 'Return date is required',
                'items.required' => 'At least one item is required',
                'items.*.product_id.required' => 'Product is required for each item',
                'items.*.quantity.required' => 'Quantity is required',
                'items.*.unit_price.required' => 'Unit price is required',
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
