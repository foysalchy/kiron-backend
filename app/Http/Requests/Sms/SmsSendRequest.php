<?php

namespace App\Http\Requests\Sms;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SmsSendRequest extends BaseCompanyRequest
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
            'customer_ids'   => ['nullable', 'array'],
            'customer_ids.*' => ['integer', 'exists:parties,id'],
            'supplier_ids'   => ['nullable', 'array'],
            'custom_numbers'   => ['nullable', 'array'],
            'supplier_ids.*' => ['integer', 'exists:parties,id'],
            'message'           => ['required', 'string'],
            'status'         => ['nullable', 'integer'],
        ]);
    }
    /**
     * Custom error messages for validation.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $customers  = $this->input('customer_ids', []);
            $suppliers  = $this->input('supplier_ids', []);
            if (empty($customers) && empty($suppliers)) {
                $validator->errors()->add('customer_ids', 'Select at least one customer or supplier.');
            }
        });
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'customer_ids.array'        => 'The customer list must be a valid format.',
            'customer_ids.*.exists'     => 'One or more selected customers do not exist.',

            'supplier_ids.array'        => 'The supplier list must be a valid format.',
            'supplier_ids.*.exists'     => 'One or more selected suppliers do not exist.',

            'subject.required'          => 'The subject is required.',
            'subject.max'               => 'The subject cannot exceed 255 characters.',

            'body.required'             => 'The message content is required.',
            'body.string'               => 'The message must be a valid text.',

            'status.integer'            => 'Status must be a valid number.',
        ]);
    }

    /**
     * Handle a failed validation attempt.
     */

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors occurred.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
