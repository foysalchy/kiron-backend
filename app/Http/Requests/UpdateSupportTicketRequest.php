<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSupportTicketRequest extends UpdateBaseCompanyRequest
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
                'support_department_id' => ['sometimes', 'required', 'exists:support_departments,id'],
                'subject'               => ['sometimes', 'required', 'string', 'max:255'],
                'description'           => ['sometimes', 'required', 'string'],
                'image'                 => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                'status'                => ['sometimes', 'integer'], 
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'subject.required'     => 'The ticket subject cannot be empty.',
                'description.required' => 'The ticket description cannot be empty.',
                'image.image'          => 'The uploaded file must be an image.',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Ticket update validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
