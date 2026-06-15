<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLeaveApplicationRequest extends BaseCompanyRequest
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
            $this->companyRules(), // BaseCompanyRequest 
            [
                'employee_id'     => ['required', 'exists:employees,id'],
                'leave_type_id'   => ['required', 'exists:leave_types,id'],
                'assign_leave_id' => ['nullable', 'exists:assign_leave_types,id'], 
                'from_date'       => ['required', 'date'],
                'to_date'         => ['required', 'date', 'after_or_equal:from_date'],
                'is_half_day'     => ['nullable' ],
                'reason'          => ['nullable', 'string', 'max:1000'],
                'documents'       => ['nullable', 'array'], 
                'documents.*'     => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:2048'], 
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
                'employee_id.required'   => 'Please select an employee.',
                'leave_type_id.required' => 'Please select the type of leave.',
                'from_date.required'     => 'Start date is required.',
                'to_date.after_or_equal' => 'The To Date must be a date after or equal to From Date.',
                'documents.*.mimes'      => 'Supported file formats: PDF, JPG, JPEG & PNG.', 
                'documents.*.max'        => 'Each file size should not exceed 2MB.', 
                'assign_leave_id.exists' => 'Selected leave allocation is invalid.',
            ]
        );
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
