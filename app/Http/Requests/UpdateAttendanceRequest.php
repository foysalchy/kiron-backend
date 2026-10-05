<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends UpdateBaseCompanyRequest
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
                    'sometimes',
                    'required',
                    Rule::exists('employees', 'id')->where('company_id', $companyId),
                ],
                'date'         => ['sometimes', 'required', 'date'],
                'in_time'      => ['nullable', 'date_format:H:i'],
                'out_time'     => ['nullable', 'date_format:H:i'],
                'grace_time'   => ['nullable', 'integer', 'min:0'],
                'status'       => [
                    'sometimes',
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
                'is_late'      => ['nullable', 'boolean'],
                'is_early_out' => ['nullable', 'boolean'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'status.in' => 'The selected status is invalid.',
                'in_time.date_format' => 'In-time must be in HH:mm format.',
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
