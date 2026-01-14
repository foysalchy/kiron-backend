<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'stock_status' => 'required|in:in_stock,out_of_stock',
            'stock_quantity' => 'required_if:stock_status,in_stock|integer|min:0',

        ];
    }

    public function messages(): array
    {
        return [
            'stock_status.required' => 'Stock status is required',
            'stock_quantity.required_if' => 'Stock quantity is required when product is in stock',


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
