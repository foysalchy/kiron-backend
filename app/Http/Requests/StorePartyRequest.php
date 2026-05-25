<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePartyRequest extends BaseCompanyRequest
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
                'type' => ['required', 'integer', Rule::in([1, 2])],
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('parties', 'email')
                        ->where('company_id', $companyId)
                        ->where('type', $this->input('type')),
                ],
                'phone' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('parties', 'phone')
                        ->where('company_id', $companyId)
                        ->where('type', $this->input('type')),
                ],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
                'alternative_phone' => ['nullable', 'string', 'max:20'],
                'gender' => ['nullable', 'string'],
                'division' => ['nullable', 'string'],
                'district' => ['nullable', 'string'],
                'thana' => ['nullable', 'string'],
                'address' => ['nullable', 'string'],
                'balance' => ['nullable', 'numeric'],
                'convert_to_customer' => ['nullable'],
                'profile' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
                'status' => ['integer'],
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
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 characters',
                'password.confirmed' => 'Password confirmation does not match',
                'email.required' => 'Email is required',
                'email.email' => 'Please provide a valid email address',
                'email.unique' => 'This email is already taken.',
                'phone.required' => 'Phone number is required',
                'phone.unique' => 'This phone is already taken.',
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
