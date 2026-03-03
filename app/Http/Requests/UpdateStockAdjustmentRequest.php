<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStockAdjustmentRequest extends UpdateBaseCompanyRequest
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
                'adjustment_date' => 'required|date',
                'warehouse_id' => 'required|exists:warehouses,id',
                'adjustment_reason' => 'required|string|in:damage,loss,found,correction,theft,expired,return',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.variation_id' => 'nullable',
                'items.*.bin_id' => 'nullable|exists:bins,id',
                'items.*.batch_number' => 'nullable|string|max:100',
                'items.*.serial_numbers' => 'nullable|array',
                'items.*.quantity_to_adjust' => 'required|integer|not_in:0',
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'adjustment_date.required' => 'Adjustment date is required',
                'warehouse_id.required' => 'Warehouse is required',
                'adjustment_reason.required' => 'Adjustment reason is required',
                'items.required' => 'At least one item is required',
                'items.*.product_id.required' => 'Product is required for each item',
                'items.*.quantity_to_adjust.required' => 'Quantity to adjust is required',
                'items.*.quantity_to_adjust.not_in' => 'Quantity cannot be zero',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
