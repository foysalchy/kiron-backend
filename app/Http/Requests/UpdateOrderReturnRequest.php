<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOrderReturnRequest extends UpdateBaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'return_date' => ['sometimes', 'date'],
            'reason' => ['nullable', 'string'],
            
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
            
            'other_charges' => ['nullable', 'numeric', 'min:0'],
            'discount_on_all' => ['nullable', 'numeric', 'min:0'],
            'coupon_discount' => ['nullable', 'numeric', 'min:0'],
            'round_off' => ['nullable', 'numeric'],
            
            'note' => ['nullable', 'string'],
            'status' => ['sometimes', 'integer', 'in:0,1,2,3,4'],
        ];
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