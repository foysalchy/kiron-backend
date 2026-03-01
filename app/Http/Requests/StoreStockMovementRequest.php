<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreStockMovementRequest extends BaseCompanyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'movement_date' => 'required|date',
                'source_warehouse_id' => 'required|exists:warehouses,id',
                'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.variation_id' => 'nullable',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.batch_number' => 'nullable|string|max:100',
                'items.*.source_bin_id' => 'nullable|exists:bins,id',
                'items.*.destination_bin_id' => 'nullable|exists:bins,id',
                'items.*.serial_numbers' => 'nullable|array',
                'items.*.serial_numbers.*' => 'string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]
        );
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),

            [
                'movement_date.required' => 'Movement date is required',
                'source_warehouse_id.required' => 'Source warehouse is required',
                'destination_warehouse_id.required' => 'Destination warehouse is required',
                'destination_warehouse_id.different' => 'Source and destination warehouses must be different',
                'items.required' => 'At least one item is required',
                'items.*.product_id.required' => 'Product is required for each item',
                'items.*.quantity.required' => 'Quantity is required for each item',
                'items.*.quantity.min' => 'Quantity must be at least 1',
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
