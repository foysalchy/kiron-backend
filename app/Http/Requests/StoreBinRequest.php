<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseCompanyRequest;

class StoreBinRequest extends BaseCompanyRequest
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
                'warehouse_id' => ['required', 'exists:warehouses,id'],
                'area_id'      => ['required', 'exists:areas,id'],
                'rack_id'      => ['required', 'exists:racks,id'],
                'cell_id'      => ['required', 'exists:cells,id'],
                'bin_code'     => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('bins')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    }),
                ],
                'name'         => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('bins')->where(function ($query) use ($companyId) {
                        return $query
                            ->where('company_id', $companyId)
                            ->where('warehouse_id', $this->warehouse_id)
                            ->where('area_id', $this->area_id)
                            ->where('rack_id', $this->rack_id)
                            ->where('cell_id', $this->cell_id);
                    }),
                ],
                'status'       => ['nullable', 'integer', 'in:0,1'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'warehouse_id.required' => 'Warehouse is required.',
                'warehouse_id.exists'   => 'Selected warehouse does not exist.',
                'bin_code.required'     => 'Bin code is required.',
                'bin_code.unique'       => 'This bin code already exists in your company.',
                'name.required'         => 'Bin name is required.',
                'name.unique'           => 'A bin with this name already exists at the selected location.',
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
