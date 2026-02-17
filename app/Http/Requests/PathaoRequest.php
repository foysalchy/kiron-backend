<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PathaoRequest extends BaseCompanyRequest
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
            'order_id'                  => ['required', 'exists:orders,id'],

            // ── Optional — auto-filled from order/customer if not sent ──
            'recipient_name'            => ['nullable', 'string', 'min:3', 'max:100'],
            'recipient_phone'           => ['nullable', 'string', 'size:11'],
            'recipient_secondary_phone' => ['nullable', 'string', 'size:11'],
            'recipient_address'         => ['nullable', 'string', 'min:10', 'max:220'],

            // ── Optional location ─────────────────────────
            'city_id'                   => ['nullable', 'integer'],
            'zone_id'                   => ['nullable', 'integer'],
            'area_id'                   => ['nullable', 'integer'],

            // ── Optional parcel info (defaults applied in service) ────
            'delivery_type'             => ['nullable', 'in:48,12'],
            'item_type'                 => ['nullable', 'in:1,2'],
            'item_quantity'             => ['nullable', 'integer', 'min:1'],
            'item_weight'               => ['nullable', 'numeric', 'min:0.5', 'max:10'],
            'product_title'             => ['nullable', 'string'],
            'special_instruction'       => ['nullable', 'string'],
            'amount_to_collect'         => ['nullable', 'integer', 'min:0'],
        ]);
    }
    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'order_id.required'    => 'System Order ID is mandatory.',
            'order_id.exists'      => 'Order not found.',
            'recipient_phone.size' => 'Phone number must be exactly 11 digits.',
            'recipient_address.min' => 'Detailed address is required (Min 10 chars).',
            'item_weight.min'      => 'Minimum parcel weight is 0.5 kg.',
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
