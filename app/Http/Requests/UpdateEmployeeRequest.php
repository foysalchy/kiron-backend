<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends UpdateBaseCompanyRequest
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
        $employeeId = $this->id;

        return array_merge(
            $this->companyRules(),
            [
                // --- Foreign Keys ---
                'department_id'      => ['sometimes', 'required', 'exists:departments,id'],
                'employee_type_id'   => ['sometimes', 'required', 'exists:employee_types,id'],
                'job_title_id'       => ['nullable', 'exists:job_titles,id'],
                'office_location_id' => ['nullable', 'exists:office_locations,id'],

                // --- General Info ---
                'first_name' => ['sometimes', 'required', 'string', 'max:100'],
                'last_name'  => ['nullable', 'string', 'max:100'],
                'nick_name'  => ['nullable', 'string', 'max:50'],
                'phone'      => ['sometimes', 'required', 'string', 'max:20'],
                'email'      => ['nullable','email','max:150',
                    Rule::unique('employees', 'email')->ignore($employeeId)
                ],
                'gender'     => ['sometimes', 'required', 'in:Male,Female,Other'],
                'dob'        => ['sometimes', 'required', 'date', 'before:today'],
                'image'      => ['nullable', 'image', 'max:2048'],

                // --- Work Info ---
                'joining_date'            => ['sometimes', 'required', 'date'],
                'payslip_generation_date' => ['sometimes', 'required', 'date'],
                'confirmation_date'       => ['nullable', 'date'],
                'in_time'                 => ['nullable', 'date_format:H:i'],
                'out_time'                => ['nullable', 'date_format:H:i'],
                'allow_flexible_time'     => ['boolean'],

                // --- Address Info ---
                'present_address'   => ['nullable', 'string'],
                'present_zip_code'  => ['nullable', 'string', 'max:20'],
                'permanent_address' => ['nullable', 'string'],
                'is_same_address'   => ['boolean'],

                // --- Status ---
                'status' => ['nullable', 'in:0,1'],
            ]
        );
    }
    /**
     * Custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'first_name.required' => 'Employee first name is required',
                'email.unique'        => 'This email address is already in use by another employee',
                'image.image'         => 'The file must be an image',
                'image.max'           => 'Image size cannot exceed 2MB',
            ]
        );
    }

    /**
     * Handle a failed validation attempt.
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
