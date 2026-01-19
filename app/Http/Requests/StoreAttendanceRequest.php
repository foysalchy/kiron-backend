<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAttendanceRequest extends BaseCompanyRequest
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
                'employee_id'  => ['required', 'exists:employees,id'],
                'date'         => ['required', 'date'],
                'in_time'      => ['nullable', 'date_format:H:i'],
                'out_time'     => ['nullable', 'date_format:H:i', 'after:in_time'],
                'grace_time'   => ['nullable', 'integer', 'min:0'],
                'status'       => ['required', 'string', 'in:Present,Absent,Weekend,Late,early-out,holiday'],
                'is_late'      => ['boolean'],
                'is_early_out' => ['boolean'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'employee_id.required' => 'Employee selection is mandatory.',
                'employee_id.exists'   => 'The selected employee does not exist.',
                'date.required'        => 'Attendance date is required.',
                'in_time.date_format'  => 'In-time must be in HH:mm format.',
                'out_time.after'       => 'Out-time must be after in-time.',
                'status.required'      => 'Attendance status is required.',
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
