<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateResignationRequest extends UpdateBaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'employee_id'          => ['sometimes', 'exists:employees,id'],
                'type'                 => ['sometimes', 'string', 'in:resignation,termination'],
                'letter_received_date' => ['nullable', 'date'],
                'resign_date'          => ['nullable', 'date'],
                'letter'               => ['sometimes', 'file', 'mimes:jpeg,png,jpg,pdf,webp', 'max:2048'],
                'resign_rule_ids'      => ['sometimes', 'array'],
                'resign_rule_ids.*'    => ['exists:resign_rules,id'],
                'reason'               => ['sometimes', 'string'],
                'activities'           => ['nullable', 'string'],
                'is_applied'           => ['sometimes', 'boolean'],
                'status'               => ['nullable', 'integer', 'in:0,1'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'employee_id.exists'    => 'The selected employee does not exist.',
                'type.in'           => 'Please select a valid resignation or termination type.',
                'resign_rule_ids.array'    => 'Resign rules must be an array of IDs.',
                'letter.max'              => 'The file size cannot exceed 2MB.',
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
