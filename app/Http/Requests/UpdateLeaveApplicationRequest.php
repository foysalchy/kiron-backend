<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLeaveApplicationRequest extends UpdateBaseCompanyRequest
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
                'employee_id'     => ['sometimes', 'exists:employees,id'],
                'leave_type_id'   => ['sometimes', 'exists:leave_types,id'],
                'assign_leave_id' => ['sometimes', 'exists:assign_leave_types,id'],
                'from_date'       => ['sometimes', 'date'],
                'to_date'         => ['sometimes', 'date', 'after_or_equal:from_date'],
                'is_half_day'     => ['nullable',],
                'reason'          => ['nullable', 'string', 'max:1000'],
                'documents'       => ['nullable', 'array'],
                'documents.*'     => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:2048'],
                'status'          => ['sometimes', 'integer'],
            ]
        );
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'to_date.after_or_equal' => 'The To Date must be a date after or equal to From Date.', //
                'documents.*.mimes'      => 'Supported file formats: PDF, JPG, JPEG & PNG.', //
                'documents.*.max'        => 'Each file size should not exceed 2MB.',
                'status.integer'         => 'Invalid status format.',
            ]
        );
    }

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
