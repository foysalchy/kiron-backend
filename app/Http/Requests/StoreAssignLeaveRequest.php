<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAssignLeaveRequest extends BaseCompanyRequest
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
                'position_id'            => ['required', 'exists:positions,id'],
                'leaves'                 => ['required', 'array', 'min:1'],
                'leaves.*.leave_type_id' => ['required', 'exists:leave_types,id'],
                'leaves.*.leave_count'   => ['required', 'numeric', 'min:0'],
                'status'                 => ['nullable', 'integer', 'in:0,1'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'position_id.required'          => 'Position selection is required',
                'leaves.required'               => 'At least one leave type must be assigned',
                'leaves.*.leave_count.required' => 'Leave count is mandatory for each selected type',
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
