<?php

namespace App\Http\Requests\Email;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmailTemplateRequest extends UpdateBaseCompanyRequest
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
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'body'    => ['sometimes', 'required', 'string'],
            'status'  => ['sometimes', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'subject.required' => 'The email subject cannot be empty.',
            'body.required'    => 'The email body cannot be empty.',
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
