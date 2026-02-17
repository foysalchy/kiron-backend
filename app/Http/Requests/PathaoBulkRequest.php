<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PathaoBulkRequest extends BaseCompanyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->companyRules(), [
            'order_ids'                            => ['required', 'array', 'min:1'],
            'order_ids.*'                 => ['required', 'exists:orders,id'],

            // Optional — auto-filled from customer if not sent
            'orders.*.recipient_name'           => ['nullable', 'string', 'min:3', 'max:100'],
            'orders.*.recipient_phone'          => ['nullable', 'string', 'size:11'],
            'orders.*.recipient_secondary_phone' => ['nullable', 'string', 'size:11'],
            'orders.*.recipient_address'        => ['nullable', 'string', 'min:10', 'max:220'],

            'orders.*.city_id'                  => ['nullable', 'integer'],
            'orders.*.zone_id'                  => ['nullable', 'integer'],
            'orders.*.area_id'                  => ['nullable', 'integer'],

            'orders.*.delivery_type'            => ['nullable', 'in:48,12'],
            'orders.*.item_type'                => ['nullable', 'in:1,2'],
            'orders.*.item_quantity'            => ['nullable', 'integer', 'min:1'],
            'orders.*.item_weight'              => ['nullable', 'numeric', 'min:0.5', 'max:10'],
            'orders.*.product_title'            => ['nullable', 'string'],
            'orders.*.special_instruction'      => ['nullable', 'string'],
            'orders.*.amount_to_collect'        => ['nullable', 'integer', 'min:0'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'order_ids.required'                => 'Orders list is required.',
            'order_ids.min'                     => 'At least one order is required.',
            'orders.*.order_id.required'     => 'Each item must have an order_id.',
            'orders.*.order_id.exists'       => 'Order :input not found.',
            'orders.*.recipient_phone.size'  => 'Phone must be exactly 11 digits.',
            'orders.*.item_weight.min'       => 'Minimum weight is 0.5 kg.',
        ]);
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed for bulk orders',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
