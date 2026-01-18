<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCouponRequest extends BaseCompanyRequest
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
                'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],

                'discount_type' => ['required', 'integer', 'in:1,2'],
                'discount_value' => ['required', 'numeric', 'min:0'],
                'max_discount_amount' => ['nullable', 'numeric', 'min:0', 'required_if:discount_type,2'],

                'min_purchase_amount' => ['nullable', 'numeric', 'min:0'],
                'usage_limit' => ['nullable', 'integer', 'min:1'],
                'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],

                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after:start_date'],

                'status' => ['nullable', 'integer', 'in:0,1'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'code.required' => 'Coupon code is required',
                'code.unique' => 'This coupon code already exists',
                'name.required' => 'Coupon name is required',

                'discount_type.required' => 'Discount type is required',
                'discount_type.in' => 'Invalid discount type',
                'discount_value.required' => 'Discount value is required',

                'max_discount_amount.required_if' => 'Max discount amount is required for percentage discount',

                'start_date.required' => 'Start date is required',
                'end_date.required' => 'End date is required',
                'end_date.after' => 'End date must be after start date',
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
