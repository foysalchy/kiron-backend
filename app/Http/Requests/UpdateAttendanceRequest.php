<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
       return array_merge(
            $this->companyRules(),
            [
                'in_time'       => ['sometimes', 'nullable', 'date_format:H:i'],
                'out_time'      => ['sometimes', 'nullable', 'date_format:H:i', 'after:in_time'],
                'working_hours' => ['sometimes', 'nullable', 'string'],
                'late_time'     => ['sometimes', 'nullable', 'date_format:H:i'],
                'over_time'     => ['sometimes', 'nullable', 'date_format:H:i'],
                'status'        => ['sometimes', 'required', 'string', 'in:Present,Absent,Weekend,Late,early-out,holiday'],
                'is_late'       => ['sometimes', 'boolean'],
                'is_early_out'  => ['sometimes', 'boolean'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'in_time.date_format'  => 'Invalid in-time format.',
                'out_time.after'       => 'Out-time must be greater than in-time.',
                'status.in'            => 'Selected status is invalid.',
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
