<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseCompanyRequest;

class StoreWarehouseRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->input('company_id') ?? $this->user()->company_id;

        return array_merge(
            $this->companyRules(),
            [
                'name'     => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('warehouses')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    }),
                ],
                'location' => ['nullable', 'string', 'max:500'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'name.required' => 'Warehouse Name is required.',
                'name.unique'   => 'A warehouse with this name already exists in your company.',
                'location.max'  => 'Location cannot exceed 500 characters.',
            ]
        );
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
