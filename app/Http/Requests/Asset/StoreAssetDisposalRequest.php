<?php

namespace App\Http\Requests\Asset;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAssetDisposalRequest extends BaseCompanyRequest
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
            'disposal_type_id' => ['required', 'integer', 'exists:disposal_types,id'],
            'date'             => ['required', 'date'],
            'amount'           => ['required', 'numeric', 'min:0'],
            'note'             => ['nullable', 'string', 'max:1000'],
            'status'           => ['nullable', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'disposal_type_id.required' => 'Please select a disposal type.',
            'disposal_type_id.exists'   => 'The selected disposal type is invalid.',
            'amount.numeric'            => 'Disposal amount must be a number.',
        ]);
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
