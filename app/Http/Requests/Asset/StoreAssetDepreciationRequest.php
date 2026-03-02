<?php

namespace App\Http\Requests\Asset;

use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreAssetDepreciationRequest extends BaseCompanyRequest
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
            'asset_id'           => ['required', 'integer', 'exists:assets,id',Rule::unique('asset_depreciations', 'asset_id')],
            'method'             => ['required', 'string'],
            'useful_life'        => ['required', 'integer', 'min:1'],
            'residual_value'     => ['required', 'numeric', 'min:0'],
            'start_date'         => ['required', 'date'],
            'status'             => ['nullable', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'asset_id.unique'    => 'This asset already has depreciation information.',
            'useful_life.min'    => 'Useful life must be at least 1 month.',
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
