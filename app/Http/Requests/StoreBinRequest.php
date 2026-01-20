<?php

namespace App\Http\Requests;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\BaseCompanyRequest;
class StoreBinRequest extends BaseCompanyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
   return array_merge(
            $this->companyRules(),
            [
            'warehouse_id' => 'required|exists:warehouses,id',
            'area_id' => 'nullable|exists:areas,id',
            'rack_id' => 'nullable|exists:racks,id',
            'cell_id' => 'nullable|exists:cells,id',
            'bin_code' => 'required|string|max:50|unique:bins,bin_code',
            'name' => 'required|string|max:255',
            'status' => 'nullable|integer|in:0,1',
        ]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
            'warehouse_id.required' => 'Warehouse is required',
            'warehouse_id.exists' => 'Selected warehouse does not exist',
            'bin_code.required' => 'Bin code is required',
            'bin_code.unique' => 'Bin code already exists',
            'name.required' => 'Bin name is required',
        ]);
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
