<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePayRollPayHeadRequest extends UpdateBaseCompanyRequest
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
            'pay_roll_id'   => ['sometimes', 'required', 'exists:pay_rolls,id'],
            'pay_head_name' => ['sometimes', 'required', 'exists:pay_heads,name'],
            'type'          => ['sometimes', 'required', 'string'],
            'amount'        => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'amount.numeric'        => 'The amount must be a number.',
            'pay_roll_id.exists'    => 'Invalid payroll selected.',
            'pay_head_name.exists'  => 'Invalid pay head name selected.',
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
