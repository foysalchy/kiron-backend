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
            'orders'   => ['required', 'array', 'min:1'],
            'orders.*.order_id'           => ['required', 'exists:orders,id'],
            'orders.*.recipient_name'     => ['required', 'string', 'min:3', 'max:100'],
            'orders.*.recipient_phone'    => ['required', 'string', 'size:11'],
            'orders.*.recipient_address'  => ['required', 'string', 'min:10', 'max:220'],
            'orders.*.delivery_type'      => ['required', 'in:48,12'],
            'orders.*.item_type'          => ['required', 'in:1,2'],
            'orders.*.item_quantity'      => ['required', 'integer', 'min:1'],
            'orders.*.item_weight'        => ['required', 'numeric', 'min:0.5', 'max:10'],
            'orders.*.product_title'      => ['nullable', 'string'],
            'orders.*.amount_to_collect'  => ['required', 'integer', 'min:0'],
            'orders.*.special_instruction'=> ['nullable', 'string'],
            'orders.*.city_id'            => ['nullable', 'integer'],
            'orders.*.zone_id'            => ['nullable', 'integer'],
            'orders.*.area_id'            => ['nullable', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'orders.required'             => 'The orders list is missing.',
            'orders.*.order_id.exists'    => 'One or more Order IDs are invalid.',
            'orders.*.recipient_phone.size' => 'Phone number must be 11 digits.',
            'orders.*.item_weight.min'    => 'Weight must be at least 0.5 kg.',
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
