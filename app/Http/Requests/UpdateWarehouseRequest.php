<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Http\Requests\UpdateBaseCompanyRequest;

class UpdateWarehouseRequest extends UpdateBaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $warehouseId = $this->route('id');
        $companyId   = $this->input('company_id') ?? $this->user()->company_id;

        return array_merge(
            $this->companyRules(),
            [
                'name'     => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('warehouses')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    })->ignore($warehouseId),
                ],
                'location' => ['nullable', 'string', 'max:500'],
                'status'   => ['sometimes', 'required', 'integer', 'in:0,1'],
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
                'status.in'     => 'Status must be 0 (Inactive) or 1 (Active).',
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
