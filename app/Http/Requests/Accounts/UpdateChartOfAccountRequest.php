<?php

namespace App\Http\Requests\Accounts;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateChartOfAccountRequest extends UpdateBaseCompanyRequest
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
        return array_merge($this->companyRules(), [
            'account_group_id' => ['sometimes', 'required', 'exists:account_groups,id'],
            'name'             => ['sometimes', 'required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'status'           => ['sometimes', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'account_group_id.exists' => 'The selected account group does not exist.',
                'name.required'           => 'The account name field is mandatory.',
            ]
        );
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
