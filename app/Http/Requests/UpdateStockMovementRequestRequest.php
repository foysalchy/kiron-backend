<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\UpdateBaseCompanyRequest;

class UpdateStockMovementRequestRequest extends UpdateBaseCompanyRequest
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
                'request_date' => 'required|date',
                'source_warehouse_id' => 'required|exists:warehouses,id',
                'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.variation_id' => 'nullable',
                'items.*.transfer_quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string|max:1000',
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'request_date.required' => 'Request date is required',
                'source_warehouse_id.required' => 'Source warehouse is required',
                'destination_warehouse_id.required' => 'Destination warehouse is required',
                'destination_warehouse_id.different' => 'Source and destination warehouses must be different',
                'items.required' => 'At least one item is required',
                'items.*.product_id.required' => 'Product is required for each item',
                'items.*.transfer_quantity.required' => 'Transfer quantity is required for each item',
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
