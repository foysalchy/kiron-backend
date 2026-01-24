<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAssignLeaveRequest extends UpdateBaseCompanyRequest
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
                'position_id'            => ['sometimes', 'exists:positions,id'],
                'leaves'                 => ['sometimes', 'array', 'min:1'],
                'leaves.*.leave_type_id' => ['required_with:leaves', 'exists:leave_types,id'],
                'leaves.*.leave_count'   => ['required_with:leaves', 'numeric', 'min:0'],
                'status'                 => ['sometimes', 'integer', 'in:0,1'],
            ]
        );
    }
        public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'position_id.exists' => 'The selected position does not exist.',
                'status.required'     => 'Status must be active (1) or inactive (0).',
            ]
        );
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Update validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
