<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use App\Models\Party;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateQuotationRequest extends BaseCompanyRequest
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
                // Check warehouse
                'warehouse_id' => [
                    'sometimes',
                    'required',
                    Rule::exists('warehouses', 'id')
                        ->where('company_id', $companyId),
                ],

                // Check customer
                'name' => ['required', 'string'],
                'phone' => ['required', 'string'],
                'email' => ['required', 'string'],
                'address' => ['required', 'string'],

                'quotation_date' => ['sometimes', 'required', 'date'],
                'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
                'reference_no' => ['nullable', 'string', 'max:255'],

                // Quotation Items
                'items' => ['sometimes', 'required', 'array', 'min:1'],
                'items.*.product_id' => [
                    'required',
                    Rule::exists('products', 'id')
                        ->where('company_id', $companyId),
                ],
                'items.*.variation_id' => [
                    'nullable',
                    'exists:product_variations,id'
                ],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.unit_price' => ['required', 'numeric', 'min:0'],
                'items.*.discount' => ['nullable', 'numeric', 'min:0'],
                'items.*.tax' => ['nullable', 'numeric', 'min:0'],
                'items.*.description' => ['nullable', 'string'],

                // Totals
                'tax_amount' => ['nullable', 'numeric', 'min:0'],
                'discount_amount' => ['nullable', 'numeric', 'min:0'],
                'shipping_charges' => ['nullable', 'numeric', 'min:0'],
                'other_charges' => ['nullable', 'numeric', 'min:0'],

                // Additional Info
                'terms_conditions' => ['nullable', 'string'],
                'note' => ['nullable', 'string'],
                'internal_note' => ['nullable', 'string'],
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
                'customer_id.required' => 'Customer is required',
                'customer_id.exists' => 'Selected customer is invalid or does not belong to your company.',
                'quotation_date.required' => 'Quotation date is required',
                'valid_until.after_or_equal' => 'Valid until date must be equal to or after quotation date',
                'items.required' => 'At least one item is required',
                'items.*.product_id.required' => 'Product is required for each item',
                'items.*.product_id.exists' => 'Selected product does not belong to the selected company.',
                'items.*.variation_id.exists' => 'Selected variation is invalid.',
                'items.*.quantity.required' => 'Quantity is required',
                'items.*.quantity.min' => 'Quantity must be at least 1',
                'items.*.unit_price.required' => 'Unit price is required',
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

    /**
     * Additional validation after base validation
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                // Validate variation belongs to product
                if (isset($item['variation_id']) && !empty($item['variation_id'])) {
                    $variation = \App\Models\ProductVariation::where('id', $item['variation_id'])
                        ->where('product_id', $item['product_id'])
                        ->first();

                    if (!$variation) {
                        $validator->errors()->add(
                            "items.{$index}.variation_id",
                            "Variation does not belong to the selected product."
                        );
                    }
                }
            }
        });
    }
}
