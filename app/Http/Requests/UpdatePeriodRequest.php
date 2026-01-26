<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePeriodRequest extends UpdateBaseCompanyRequest
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
                'period_type_id' => ['sometimes', 'exists:period_types,id'],
                'periods'        => ['sometimes', 'array', 'min:1'],
                'periods.*.id'          => ['sometimes', 'integer', 'exists:bill_periods,id'],
                'periods.*.period_name' => ['sometimes', 'string', 'max:255'],
                'periods.*.start_date'  => ['sometimes', 'date'],
                'periods.*.end_date'    => ['sometimes', 'date', 'after:periods.*.start_date'],
                'periods.*.issue_date'  => ['sometimes', 'date', 'after_or_equal:periods.*.end_date'],
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
                'period_type_id.exists'   => 'Selected period type is invalid.',
                'periods.*.end_date.after' => 'End date must be after the start date.',
                'periods.*.issue_date.after_or_equal' => 'Issue date cannot be before the end date.',
                'periods.*.id.exists'     => 'One of the selected period records is invalid.',
            ]
        );
    }

    /**
     * Return JSON response on validation failure
     */
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
