<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDepartmentRequest extends UpdateBaseCompanyRequest
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
        $currentId = $this->route('id');

        return array_merge($this->companyRules(), [
            'name'              => ['sometimes', 'required', 'string', 'max:255'],
            'code'              => ['sometimes', 'required', 'string', 'max:50'],
            'parent_department' => [
                'nullable',
                'string',
                'max:255',
                $currentId ? 'not_in:' . $currentId : '',
            ],
            'in_charge'         => ['nullable', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'status'            => ['sometimes', 'integer', 'in:0,1'],
        ]);
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required'                => 'Department name is required',
                'code.required'                => 'Department code is required',
                'parent_department.not_in'     => 'A department cannot be its own parent department',
            ]
        );
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
