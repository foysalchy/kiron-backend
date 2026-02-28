<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PayrollSettingRequest extends FormRequest
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
        return [
            'late_days_for_penalty'    => ['required', 'integer', 'min:1'],
            'penalty_amount_in_days'   => ['required', 'numeric', 'min:0'],
            'has_overtime_allowance'   => ['required', 'boolean'],
            'overtime_rate_multiplier' => ['required', 'numeric', 'min:0'],
            'standard_working_hours'   => ['required', 'integer', 'min:1', 'max:24'],
        ];
    }

    public function messages(): array
    {
        return [
            'late_days_for_penalty.required'      => 'Late days for penalty is required.',
            'late_days_for_penalty.integer'        => 'Late days for penalty must be a whole number.',
            'late_days_for_penalty.min'            => 'Late days for penalty must be at least 1.',

            'penalty_amount_in_days.required'      => 'Penalty amount in days is required.',
            'penalty_amount_in_days.numeric'       => 'Penalty amount must be a number.',
            'penalty_amount_in_days.min'           => 'Penalty amount must be at least 0.',

            'has_overtime_allowance.required'      => 'Overtime allowance setting is required.',
            'has_overtime_allowance.boolean'       => 'Overtime allowance must be true or false.',

            'overtime_rate_multiplier.required'    => 'Overtime rate multiplier is required.',
            'overtime_rate_multiplier.numeric'     => 'Overtime rate multiplier must be a number.',
            'overtime_rate_multiplier.min'         => 'Overtime rate multiplier must be at least 0.',

            'standard_working_hours.required'      => 'Standard working hours is required.',
            'standard_working_hours.integer'       => 'Standard working hours must be a whole number.',
            'standard_working_hours.min'           => 'Standard working hours must be at least 1.',
            'standard_working_hours.max'           => 'Standard working hours cannot exceed 24.',
        ];
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