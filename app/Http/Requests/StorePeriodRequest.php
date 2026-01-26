<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePeriodRequest extends BaseCompanyRequest
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
            'period_type_id' => ['required', 'exists:period_types,id'],
            'periods'        => ['required', 'array', 'min:1'],
            'periods.*.period_name' => ['required', 'string'], // Jan-24 To Apr-24
            'periods.*.start_date'  => ['required', 'date'],
            'periods.*.end_date'    => ['required', 'date', 'after:periods.*.start_date'],
            'periods.*.issue_date'  => ['required', 'date'],
        ]);
    }
    /**
     * Custom validation messages for bulk data
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'period_type_id.required' => 'Please select a valid period type.',
                'periods.required'        => 'No periods were provided.',
                'periods.*.period_name.required' => 'Each period must have a name (e.g., Jan-24 To Apr-24).',
                'periods.*.start_date.required'  => 'Start date is required for all rows.',
                'periods.*.end_date.after'       => 'End date must be a date after the start date.',
                'periods.*.issue_date.after_or_equal' => 'Issue date cannot be before the end date.',
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
