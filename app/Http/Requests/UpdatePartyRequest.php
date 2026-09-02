<?php

namespace App\Http\Requests;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePartyRequest extends UpdateBaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $companyId = $user->isSuperAdmin()
            ? $this->input('company_id')
            : $user->company_id;
        return array_merge(
            $this->companyRules(),
            [
                'type' => ['sometimes', 'required', 'integer', Rule::in([1, 2])],
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    Rule::unique('parties', 'email')
                        ->ignore($this->route('id'))
                        ->where('company_id', $companyId)
                        ->where('type', $this->input('type')),
                ],
                'phone' => [
                    'sometimes',
                    'string',
                    'max:20',
                    Rule::unique('parties', 'phone')
                        ->ignore($this->route('id'))
                        ->where('company_id', $companyId)
                        ->where('type', $this->input('type')),
                ],
                'alternative_phone' => ['nullable', 'string', 'max:20'],
                'gender' => ['nullable', 'string'],
                'division' => ['nullable', 'string'],
                'district' => ['nullable', 'string'],
                'thana' => ['nullable', 'string'],
                'address' => ['nullable', 'string'],
                'balance' => ['nullable', 'numeric'],
                'credit_limit' => ['nullable', 'numeric', 'min:0'],
                'credit_days' => ['nullable', 'integer', 'min:0'],
                'risk_level' => ['nullable', 'string', Rule::in(['low', 'medium', 'high', 'blacklisted'])],
                'profile' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
                'status' => ['nullable'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'type.required' => 'Type is required',
                'type.in' => 'Type must be 1 (Supplier) or 2 (Customer)',
                'name.required' => 'Name is required',
                'email.email' => 'Please provide a valid email address',
                'phone.required' => 'Phone number is required',
                'profile.image' => 'Profile must be an image file',
                'profile.max' => 'Profile size cannot exceed 2MB',
                'balance.numeric' => 'Balance must be a number',
            ]
        );
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
