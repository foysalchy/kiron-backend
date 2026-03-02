<?php

namespace App\Http\Requests\Asset;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAssetPurchaseRequest extends BaseCompanyRequest
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
            'asset_id'       => ['required', 'integer', 'exists:assets,id'],
            'supplier_id'    => ['nullable', 'integer', 'exists:parties,id'],
            'purchase_date'  => ['required', 'date', 'before_or_equal:today'],
            'purchase_cost'  => ['required', 'numeric', 'min:0'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'status'         => ['nullable', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'asset_id.required'     => 'Please select an asset.',
            'asset_id.exists'       => 'The selected asset is invalid.',
            'purchase_date.required'=> 'Purchase date is required.',
            'purchase_cost.required'=> 'Purchase cost is required.',
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
