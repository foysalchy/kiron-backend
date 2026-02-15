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
            'recipient_name'            => ['required', 'string', 'min:3', 'max:100'],
            'recipient_phone'           => ['required', 'string', 'size:11'],
            'recipient_secondary_phone' => ['nullable', 'string', 'size:11'],
            'recipient_address'         => ['required', 'string', 'min:10', 'max:220'],
            'city_id'                   => ['nullable', 'integer'],
            'zone_id'                   => ['nullable', 'integer'],
            'area_id'                   => ['nullable', 'integer'],
            'delivery_type'             => ['required', 'in:48,12'],
            'item_type'                 => ['required', 'in:1,2'],
            'item_quantity'             => ['required', 'integer', 'min:1'],
            'item_weight'               => ['required', 'numeric', 'min:0.5', 'max:10'],
            'product_title'             => ['nullable', 'string'], // item_description
            'special_instruction'       => ['nullable', 'string'],
            'amount_to_collect'         => ['required', 'integer', 'min:0'],
        ]);
    }
    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'order_id.required'       => 'System Order ID is mandatory.',
            'product_title.required'  => 'Product name/title is required for Pathao.',
            'recipient_phone.size'    => 'Phone number must be exactly 11 digits.',
            'recipient_address.min'   => 'Detailed address is required (Min 10 chars).',
            'item_weight.min'         => 'Minimum parcel weight is 0.5 kg.',
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
