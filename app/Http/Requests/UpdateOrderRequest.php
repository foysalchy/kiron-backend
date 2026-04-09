<?php

namespace App\Http\Requests;

use App\Http\Requests\UpdateBaseCompanyRequest;
use App\Models\Product;
use App\Rules\UniqueProductIds;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;


class UpdateOrderRequest extends UpdateBaseCompanyRequest
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
                'type' => [
                    'required'
                ],
                'warehouse_id' => [
                    'required',
                    Rule::exists('warehouses', 'id')
                        ->where('company_id', $companyId),
                ],
                'customer_id' => ['nullable',  Rule::exists('parties', 'id')
                    ->where('company_id', $companyId)
                    ->where('type', 2)],
                'is_walk_in' => ['nullable', 'boolean'],

                'items' => ['sometimes', 'array', 'min:1'],
                'items.*.product_id' => [
                    'required',
                    Rule::exists('products', 'id')
                        ->where('company_id', $companyId),
                ],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0'],
                'items.*.discount' => ['nullable', 'numeric', 'min:0'],
                'items.*.tax_group_id' => ['nullable', 'integer'],
                'items.*.tax' => ['nullable', 'numeric', 'min:0'],
                'items.*.variation_id' => ['nullable'],


                'other_charges' => ['nullable', 'numeric', 'min:0'],
                'discount_on_all' => ['nullable', 'numeric', 'min:0'],
                'round_off' => ['nullable', 'numeric'],

                'coupon_code' => ['nullable', 'string', 'exists:coupons,code'],

                'payments' => ['nullable', 'array'],
                'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
                'payments.*.payment_method' => ['required_with:payments', 'string', 'in:cash,bank,card,cheque,mobile_banking'],
                'payments.*.reference_no' => ['nullable', 'string'],
                'payments.*.note' => ['nullable', 'string'],
                'shipping_address' => 'nullable|array',
                'shipping_address.name' => 'nullable|string',
                'shipping_address.phone' => 'nullable|string',
                'shipping_address.division' => 'nullable|string',
                'shipping_address.district' => 'nullable|string',
                'shipping_address.thana' => 'nullable|string',
                'shipping_address.address' => 'nullable|string',
                'hold_ref' => ['nullable', 'string'],
                'note' => ['nullable', 'string'],
                'status' => ['nullable'],
            ]
        );
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            // 1️⃣ Either customer_id or is_walk_in must be provided
            if (!$this->customer_id && !$this->is_walk_in) {
                $validator->errors()->add(
                    'customer_id',
                    'Either customer or walk-in flag is required'
                );
            }

            // 2️⃣ Stock quantity validation
            if ($this->has('items') && is_array($this->items)) {

                $user = $this->user();
                $companyId = $user->isSuperAdmin()
                    ? $this->input('company_id')
                    : $user->company_id;

                foreach ($this->items as $index => $item) {

                    if (!isset($item['product_id'], $item['quantity'])) {
                        continue;
                    }

                    $product = Product::where('id', $item['product_id'])
                        ->where('company_id', $companyId)
                        ->first();

                    if (! $product) {
                        continue; // product exists rule already handles this
                    }

                    if ($item['quantity'] > $product->available_stock) {
                        $validator->errors()->add(
                            "items.$index.quantity",
                            "Requested quantity ({$item['quantity']}) exceeds available stock ({$product->available_stock})."
                        );
                    }
                }
            }
        });
    }


    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'warehouse_id.required' => 'Warehouse is required',
                'warehouse_id.exists' => 'Selected warehouse does not belong to your company.',
                'customer_id.exists'  => 'Selected customer is invalid or does not belong to your company.',
                'items.required' => 'At least one item is required',
                'items.*.product_id.required' => 'Product is required for each item',
                'items.*.product_id.exists' =>
                'Selected product does not belong to the selected company.',
                'items.*.quantity.required' => 'Quantity is required',
                'items.*.unit_price.required' => 'Unit price is required',
                'payments.required' => 'At least one payment is required',
                'payments.*.amount.required' => 'Payment amount is required',
                'payments.*.payment_method.required' => 'Payment method is required',
                'payments.*.payment_method.in' => 'Invalid payment method',
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
