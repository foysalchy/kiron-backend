<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSmsSettingRequest extends BaseCompanyRequest
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
        return array_merge(
            $this->companyRules(),
            [
                'settings'                  => ['required', 'array'],
                'settings.*.event_name'     => ['required', 'string'],
                'settings.*.message'        => ['required', 'string'],
                'settings.*.status'         => ['required', 'integer', 'in:0,1'],
            ]
        );
    }
    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'event_name.required' => 'The SMS event name is required (e.g., order_place).',
                'message.required'    => 'The SMS message content cannot be empty.',
                'status.in'           => 'Status must be either 0 (Inactive) or 1 (Active).',
            ]
        );
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
