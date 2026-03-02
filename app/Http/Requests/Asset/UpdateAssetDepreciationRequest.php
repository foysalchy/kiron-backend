<?php

namespace App\Http\Requests\Asset;

use App\Http\Requests\UpdateBaseCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAssetDepreciationRequest extends UpdateBaseCompanyRequest
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
            'asset_id'           => ['sometimes', 'integer', 'exists:assets,id'],
            'method'             => ['sometimes', 'string'],
            'useful_life'        => ['sometimes', 'integer', 'min:1'],
            'residual_value'     => ['sometimes', 'numeric', 'min:0'],
            'start_date'         => ['sometimes', 'date'],
            'status'             => ['sometimes', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'asset_id.unique'    => 'This asset already has depreciation information.',
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
