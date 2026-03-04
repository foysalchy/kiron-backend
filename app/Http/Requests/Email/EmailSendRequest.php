<?php

namespace App\Http\Requests\Email;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmailSendRequest extends BaseCompanyRequest
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
            'subject'        => ['required', 'string', 'max:255'],
            'body'           => ['required', 'string'],
            'status'         => ['nullable', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'customer_ids.required' => 'Please select at least one recipient.',
            'subject.required'      => 'Email subject is required.',
            'body.required'         => 'Email content body is required.',
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors occurred.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
