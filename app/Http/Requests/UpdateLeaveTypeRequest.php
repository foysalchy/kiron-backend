<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLeaveTypeRequest extends UpdateBaseCompanyRequest
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
                'name'          => ['sometimes', 'string', 'max:255'],
                'short_code'    => ['nullable', 'string', 'max:50'],
                'display_order' => ['sometimes', 'integer', 'min:1'],
                'description'   => ['nullable', 'string'],
                'from_date'     => ['nullable', 'date'],
                'to_date'       => ['nullable', 'date', 'after_or_equal:from_date'],
                'status'        => ['sometimes', 'integer'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'to_date.after_or_equal' => 'To Date must be a date after or equal to From Date',
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
