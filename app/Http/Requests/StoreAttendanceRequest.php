<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

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
        $user = $this->user();
        $companyId = $user->isSuperAdmin() ? $this->input('company_id') : $user->company_id;
        return array_merge(
            $this->companyRules(),
            [
                'employee_id' => [
                    'required',
                    Rule::exists('employees', 'id')->where('company_id', $companyId),
                ],
                'date'         => ['required', 'date'],
                'in_time'      => ['nullable', 'date_format:H:i'],
                'out_time'     => ['nullable', 'date_format:H:i', 'after:in_time'],
                'grace_time'   => ['nullable', 'integer', 'min:0'],
                'status'       => [
                    'required',
                    Rule::in([
                        Attendance::STATUS_ABSENT,
                        Attendance::STATUS_PRESENT,
                        Attendance::STATUS_WEEKEND,
                        Attendance::STATUS_LATE,
                        Attendance::STATUS_EARLY_OUT,
                        Attendance::STATUS_HOLIDAY,
                        Attendance::STATUS_LEAVE
                    ])
                ],
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
                'employee_id.exists'   => 'The selected employee does not belong to the company.',
                'date.required'        => 'Attendance date is required.',
                'status.required'      => 'Attendance status is required.',
                'status.in'            => 'Invalid attendance status provided.',
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
