<?php
namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEmployeeRequest extends BaseCompanyRequest
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
                // --- Foreign Keys ---
                'department_id'           => ['required', 'exists:departments,id'],
                'employee_type_id'        => ['required', 'exists:employee_types,id'],
                'job_title_id'            => ['nullable', 'exists:job_titles,id'],
                'office_location_id'      => ['nullable', 'exists:office_locations,id'],

                // --- General Info ---
                'first_name'              => ['required', 'string', 'max:100'],
                'last_name'               => ['nullable', 'string', 'max:100'],
                'nick_name'               => ['nullable', 'string', 'max:50'],
                'phone'                   => ['required', 'string', 'max:20'],
                'email'                   => ['nullable', 'email', 'unique:employees,email', 'max:150'],
                'gender'                  => ['required', 'in:Male,Female,Other'],
                'dob'                     => ['required', 'date', 'before:today'],
                'image'                   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],

                // --- Work Info ---
                'joining_date'            => ['required', 'date'],
                'payslip_generation_date' => ['required', 'date'],
                'confirmation_date'       => ['nullable', 'date', 'after_or_equal:joining_date'],
                'in_time'                 => ['nullable', 'date_format:H:i'],
                'out_time'                => ['nullable', 'date_format:H:i'],
                'allow_flexible_time'     => ['boolean'],

                // --- Address Info ---
                'present_address'         => ['nullable', 'string'],
                'present_zip_code'        => ['nullable', 'string', 'max:20'],
                'permanent_address'       => ['nullable', 'string'],
                'is_same_address'         => ['boolean'],

                // --- Status ---
                'status'                  => ['nullable', 'in:0,1'],
            ]
        );
    }
    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'department_id.required'    => 'Please select a department.',
                'employee_type_id.required' => 'Employee type is required.',
                'first_name.required'       => 'Employee first name is required.',
                'phone.required'            => 'Phone number is required.',
                'email.unique'              => 'This email is already registered.',
                'dob.required'              => 'Date of birth is required.',
                'joining_date.required'     => 'Joining date is required.',
                'image.image'               => 'The file must be an image.',
                'image.max'                 => 'The image size cannot exceed 2MB.',
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
