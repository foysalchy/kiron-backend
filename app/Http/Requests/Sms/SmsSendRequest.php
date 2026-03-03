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
            'customer_ids'   => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['required', 'integer', 'exists:parties,id'],
            'message'        => ['required', 'string', 'min:3', 'max:1000'],
            'status'         => ['nullable', 'integer'],
        ]);
    }
    /**
     * Custom error messages for validation.
     */
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'customer_ids.required' => 'Please select at least one customer.',
            'customer_ids.array'    => 'The customer list must be a valid format.',
            'customer_ids.*.exists' => 'One or more selected customers do not exist.',
            'message.required'      => 'The SMS message content is required.',
            'message.min'           => 'The message must be at least 3 characters long.',
            'message.max'           => 'The message cannot exceed 1000 characters.',
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
