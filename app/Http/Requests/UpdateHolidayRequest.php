<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateHolidayRequest extends UpdateBaseCompanyRequest
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
                'name'           => ['sometimes', 'string', 'max:255'],
                'description'    => ['nullable', 'string'],
                'number_of_days' => ['sometimes', 'integer', 'min:1'],
                'from_date'      => ['sometimes', 'date'],
                'to_date'        => ['sometimes', 'date', 'after_or_equal:from_date'],
                'theme_color'    => ['nullable', 'string'],
                'status'         => ['sometimes', 'integer'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'to_date.after_or_equal' => 'End date cannot be earlier than start date',
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
