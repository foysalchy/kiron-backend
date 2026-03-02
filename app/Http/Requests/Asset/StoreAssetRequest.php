<?php

namespace App\Http\Requests\Asset;

use App\Enums\Status;
use App\Http\Requests\BaseCompanyRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends BaseCompanyRequest
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
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'manager_id'        => ['nullable', 'exists:users,id'],
            'image'             => ['nullable', 'image', 'mimes:jpg,png,webp', 'max:2048'], // Max 2MB
            'name'              => ['required', 'string', 'max:255'],
            'asset_tag'         => ['nullable','string',Rule::unique('assets', 'asset_tag')->where('company_id', $this->company_id)],
            'serial_number'     => ['nullable', 'string', 'max:255'],
            'model_number'      => ['nullable', 'string', 'max:255'],
            'asset_location'    => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'status'            => ['nullable', 'integer'],
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'asset_category_id.required' => 'Please select an asset category.',
            'asset_location.required'    => 'Asset location is required.',
            'asset_tag.unique'           => 'This asset tag is already in use within this company.',
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
