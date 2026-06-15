<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreResignationRequest extends BaseCompanyRequest
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
                'employee_id'          => ['required', 'exists:employees,id'],
                'type'                 => ['required', 'string', 'in:resignation,termination'],
                'letter_received_date' => ['nullable', 'date'],
                'resign_date'          => ['nullable', 'date'],
                'letter'               => ['required', 'file', 'mimes:jpeg,png,jpg,pdf,webp', 'max:2048'],
                'resign_rule_ids'      => ['required', 'array'],
                'resign_rule_ids.*'    => ['exists:resign_rules,id'],
                'reason'               => ['required', 'string'],
                'activities'           => ['nullable', 'string'],
                'is_applied'           => ['required', 'boolean'],
                'status'               => ['nullable', 'integer', 'in:0,1'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'employee_id.required'    => 'Employee selection is required.',
                'type.required'           => 'Please select resignation or termination.',
                'resign_rule_ids.required' => 'Please select at least one resign rule.',
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
