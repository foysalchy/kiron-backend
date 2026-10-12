<?php

namespace App\Http\Requests\Asset;

use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAssetDisposalRequest extends UpdateBaseCompanyRequest
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
            'asset_id'         => ['sometimes', 'required', 'integer', 'exists:assets,id'],
            'disposal_type_id' => ['sometimes', 'required', 'integer', 'exists:disposal_types,id'],
            'date'             => ['sometimes', 'required', 'date'],
            'amount'           => ['sometimes', 'required', 'numeric', 'min:0'],
            'note'             => ['nullable', 'string', 'max:1000'],
            'status'           => ['sometimes', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'asset_id.required'         => 'Please select an asset.',
            'asset_id.exists'           => 'The selected asset is invalid.',
            'disposal_type_id.required' => 'Please select a disposal type.',
            'disposal_type_id.exists'   => 'The selected disposal type is invalid.',
            'date.required'             => 'Disposal date is required.',
            'date.date'                 => 'Disposal date must be a valid date.',
            'amount.required'           => 'Amount is required.',
            'amount.numeric'            => 'Disposal amount must be a number.',
            'amount.min'                => 'The disposal amount cannot be less than 0.',
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
