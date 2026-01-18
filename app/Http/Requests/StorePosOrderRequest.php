<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;


class StorePosOrderRequest extends BaseCompanyRequest
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
                'customer_id' => ['nullable',  Rule::exists('parties', 'id')
                    ->where('company_id', $companyId)
                    ->where('type', 2)],
                'is_walk_in' => ['nullable', 'boolean'],

                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => [
                    'required',
                    Rule::exists('products', 'id')
                        ->where('company_id', $companyId),
                ],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0'],
                'items.*.discount' => ['nullable', 'numeric', 'min:0'],
                'items.*.tax' => ['nullable', 'numeric', 'min:0'],

                'coupon_code' => ['nullable', 'string', 'exists:coupons,code'],

                'payments' => ['required', 'array', 'min:1'],
                'payments.*.amount' => ['required', 'numeric', 'min:0'],
                'payments.*.payment_method' => ['required', 'string', 'in:cash,card,bank,mobile_banking'],
                'payments.*.reference_no' => ['nullable', 'string'],
                'payments.*.note' => ['nullable', 'string'],

                'hold_ref' => ['nullable', 'string'],
                'note' => ['nullable', 'string'],
            ]
        );
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Either customer_id or is_walk_in must be provided
            if (!$this->customer_id && !$this->is_walk_in) {
                $validator->errors()->add('customer_id', 'Either customer or walk-in flag is required');
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
