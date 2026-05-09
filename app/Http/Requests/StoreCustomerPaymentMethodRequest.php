<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCustomerPaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:150'],
            'type'           => ['required', 'string'],
            'icon'           => ['nullable', 'image', 'max:2048'],
            'method_details' => ['nullable', 'array'],
            'account_holder' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'contact_name'   => ['required', 'string', 'max:150'],
            'phone'          => ['required', 'string', 'max:20'],
            'status'         => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'The method name (e.g., bKash) is required.',
            'method_details.array'   => 'Method details must be a valid JSON/Array.',
            'contact_name.required'  => 'Contact person name is required.',
            'phone.required'         => 'Contact phone number is required.',
            'icon.image'             => 'The icon must be an image file.',
        ];
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