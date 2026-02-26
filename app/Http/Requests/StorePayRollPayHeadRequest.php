<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePayRollPayHeadRequest extends BaseCompanyRequest
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
            'pay_roll_id' => ['required', 'exists:pay_rolls,id'],

            'pay_head_id' => [
                'required',
                'exists:pay_heads,id',
                Rule::unique('pay_roll_pay_heads')
                    ->where(fn($q) => $q->where('pay_roll_id', $this->pay_roll_id)),
            ],

            'type'   => ['required', 'string', 'max:50'], // e.g., 'amount'
            'amount' => ['required', 'numeric', 'min:0'],
        ]);
    }

    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'pay_roll_id.required' => 'Please select a payroll.',
            'pay_roll_id.exists'   => 'The selected payroll is invalid.',

            'pay_head_id.required' => 'Please select a pay head.',
            'pay_head_id.exists'   => 'The selected pay head name does not exist.',
            'pay_head_id.unique'   => 'This pay head is already added to this payroll.',

            'amount.required'      => 'Amount is required.',
            'amount.numeric'       => 'Amount must be a valid number.',
        ]);
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
