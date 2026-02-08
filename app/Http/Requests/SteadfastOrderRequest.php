<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SteadfastOrderRequest extends BaseCompanyRequest
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
            'order_id'          => ['required', 'exists:orders,id'],
            'cod_amount'        => ['sometimes', 'numeric', 'min:0'],
            'recipient_phone'   => ['sometimes', 'string', 'digits:11'],
            'item_description'  => ['nullable', 'string', 'max:250'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'order_id.exists' => 'The selected order is invalid or does not belong to your company.',
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
