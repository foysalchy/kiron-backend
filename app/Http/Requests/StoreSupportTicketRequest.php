<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSupportTicketRequest extends BaseCompanyRequest
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
                'support_department_id' => ['required','exists:support_departments,id'],
                'subject' => ['required','string','max:255'],
                'description' => ['required','string'],
                'image' => ['nullable','image','mimes:jpeg,png,jpg,gif','max:2048' ],
            ]
        );
    }
    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'support_department_id.required' => 'Please select a department.',
            'support_department_id.exists'   => 'The selected department is invalid.',
            'subject.required'               => 'A subject is required for the ticket.',
            'description.required'           => 'Please provide a detailed description of your issue.',
            'image.image'                    => 'The file must be an image.',
            'image.max'                      => 'The image size should not exceed 2MB.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
