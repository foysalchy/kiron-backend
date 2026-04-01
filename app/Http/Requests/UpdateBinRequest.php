<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Http\Requests\UpdateBaseCompanyRequest;

class UpdateBinRequest extends UpdateBaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $binId     = $this->route('id');
        $companyId = $this->input('company_id') ?? $this->user()->company_id;

        return array_merge(
            $this->companyRules(),
            [
                'warehouse_id' => ['sometimes', 'required', 'exists:warehouses,id'],
                'area_id'      => ['sometimes', 'exists:areas,id'],
                'rack_id'      => ['sometimes', 'exists:racks,id'],
                'cell_id'      => ['sometimes', 'exists:cells,id'],
                'name'         => [
                    'sometimes',
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
                    })->ignore($binId),
                ],
                'status'       => ['sometimes', 'integer', 'in:0,1'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'warehouse_id.exists' => 'Selected warehouse does not exist.',
                'name.required'       => 'Bin name is required.',
                'name.unique'         => 'A bin with this name already exists at the selected location.',
                'status.in'           => 'Status must be 0 (Inactive) or 1 (Active).',
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
