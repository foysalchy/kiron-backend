<?php

namespace App\Http\Requests;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCustomerPaymentMethodRequest extends UpdateBaseCompanyRequest
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
            'name' => ['sometimes', 'required'],
            'icon'           => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'method_details' => ['nullable', 'array'],
            'contact_name'   => ['sometimes', 'required', 'string', 'max:255'],
            'phone'          => ['sometimes', 'required', 'string', 'max:20'],
            'account_holder' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'status'         => ['sometimes', 'integer', 'in:0,1'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'contact_name.required' => 'Contact name is required',
            'phone.required'        => 'Phone number is required',
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
