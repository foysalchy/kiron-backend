<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCourierRequest extends BaseCompanyRequest
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
        $companyId = $this->input('company_id') ?? $this->user()->company_id;

        return array_merge(
            $this->companyRules(),
            [
                'name' => ['required', ],
                'method_details'    => ['nullable', 'array'],
                'contact_name'      => ['nullable', 'string', 'max:255'],
                'phone'             => ['nullable', 'string', 'max:20'],
                'location'          => ['nullable', 'string', 'max:500'],
                'status'            => ['nullable'],
            ]
        );
    }
    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'courier_method_id.required' => 'Please select a courier method.',
                'courier_method_id.exists'   => 'The selected courier method is invalid.',
                'method_details.array'       => 'Method details must be a valid JSON/Array.',
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
