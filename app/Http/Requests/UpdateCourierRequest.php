<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCourierRequest extends UpdateBaseCompanyRequest
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
            'courier_method_id' => ['sometimes', 'required', 'exists:courier_methods,id'],
            'method_details'    => ['nullable', 'array'],
            'contact_name'      => ['sometimes', 'required', 'string', 'max:255'],
            'phone'             => ['sometimes', 'required', 'string', 'max:20'],
            'location'          => ['nullable', 'string', 'max:500'],
            'status'            => ['sometimes', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'courier_method_id.required' => 'Courier method is required',
                'courier_method_id.exists'   => 'The selected courier method is invalid',
                'contact_name.required'      => 'Contact name is required',
                'phone.required'             => 'Phone number is required',
            ]
        );
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
