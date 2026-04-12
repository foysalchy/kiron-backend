<?php
namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCustomerPaymentMethodRequest extends BaseCompanyRequest
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
            'name' => ['required'],
            'icon'           => ['nullable', 'image', 'max:2048'],
            'method_details' => ['nullable', 'array'],
            'account_holder' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'contact_name'   => ['required', 'string', 'max:150'],
            'phone'          => ['required', 'string', 'max:20'],
            'status'         => ['nullable', 'integer'],
        ]);
    }
    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'method_name.required'    => 'The method name (e.g., bKash) is required.',
            'method_details.array'       => 'Method details must be a valid JSON/Array.',
            'contact_name.required'   => 'Contact person name is required.',
            'phone.required'          => 'Contact phone number is required.',
            'icon.image'              => 'The icon must be an image file.',
        ]);
    }

    /**
     * Handle failed validation
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
