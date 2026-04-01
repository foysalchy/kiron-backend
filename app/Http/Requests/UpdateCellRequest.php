<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\UpdateBaseCompanyRequest;
use Illuminate\Validation\Rule;

class UpdateCellRequest extends UpdateBaseCompanyRequest
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
        return array_merge(
            $this->companyRules(),
            [
                'warehouse_id' => ['sometimes', 'required', 'exists:warehouses,id'],
                'area_id' => ['sometimes', 'required', 'exists:areas,id'],
                'rack_id' => ['sometimes', 'required', 'exists:racks,id'],
                'name'         => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('cells')->where(function ($query) {
                        return $query
                            ->where('warehouse_id', $this->warehouse_id)
                            ->where('area_id', $this->area_id)
                            ->where('rack_id', $this->rack_id);
                    })->ignore($this->route('id')),
                ],
                'status'  => ['sometimes', 'required', 'integer', 'in:0,1'],
            ]
        );
    }
    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'rack_id.exists' => 'The selected rack does not exist.',
                'name.required'   => 'The cell name is mandatory.',
                'name.unique'     => 'A cell with this name already exists in the selected rack.',
                'status.in'      => 'Status must be 1 for Active or 0 for Inactive.',
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
