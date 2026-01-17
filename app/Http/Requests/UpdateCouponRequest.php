<?php

namespace App\Http\Requests;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCouponRequest extends UpdateBaseCompanyRequest
{
     public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('id');

       return array_merge(
            $this->companyRules(),
            [
            'code' => ['sometimes', 'string', 'max:50', 'unique:coupons,code,' . $couponId],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            
            'discount_type' => ['sometimes', 'integer', 'in:1,2'],
            'discount_value' => ['sometimes', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            
            'min_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],
            
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            
            'status' => ['sometimes', 'integer', 'in:0,1'],
        ]);
    }

    public function messages(): array
    {
           return array_merge(
            $this->companyMessages(),
            [
            'code.unique' => 'This coupon code already exists',
            'discount_type.in' => 'Invalid discount type',
            'end_date.after' => 'End date must be after start date',
        ]);
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
