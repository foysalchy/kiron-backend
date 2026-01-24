<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreHolidayRequest extends BaseCompanyRequest
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
                'name'           => ['required', 'string', 'max:255'],
                'description'    => ['nullable', 'string'],
                'number_of_days' => ['required', 'integer', 'min:1'],
                'from_date'      => ['required', 'date'],
                'to_date'        => ['required', 'date', 'after_or_equal:from_date'],
                'theme_color'    => ['nullable', 'string'],
                'status'         => ['nullable', 'integer'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required'           => 'Holiday name is required',
                'number_of_days.required' => 'Number of days is required',
                'from_date.required'      => 'Please select a start date',
                'to_date.required'        => 'Please select an end date',
                'to_date.after_or_equal'  => 'End date must be greater than or equal to start date',
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
