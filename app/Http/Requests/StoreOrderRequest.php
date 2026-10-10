<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;


class StoreOrderRequest extends BaseCompanyRequest
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
                    'nullable',
                    Rule::exists('warehouses', 'id')
                        ->where('company_id', $companyId),
                ],
                'warehouse_info' => ['nullable', 'array'],
                'customer_id' => ['nullable',  Rule::exists('parties', 'id')
                    ->where('company_id', $companyId)
                    ->where('type', 2)],
                'is_walk_in' => ['nullable', 'boolean'],
                'source' => ['nullable', 'string', 'max:50'],
                'walk_in_customer'        => ['nullable', 'array'],
                'walk_in_customer.name'   => ['nullable', 'string', 'max:255'],
                'walk_in_customer.phone'  => ['nullable', 'string', 'max:20'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => [
                    'required',
                    Rule::exists('products', 'id')
                        ->where('company_id', $companyId),
                ],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0'],
                'items.*.bin_id' => ['nullable', 'integer', Rule::exists('bins', 'id')->where('company_id', $companyId)],
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

                'hold_ref' => ['nullable', 'string'],
                'note' => ['nullable', 'string'],
                'status' => ['nullable'],
                'is_due' => ['nullable'],
                'due_amount' => ['nullable', 'numeric'],
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
