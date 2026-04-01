<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Http\Requests\UpdateBaseCompanyRequest;

class UpdateRackRequest extends UpdateBaseCompanyRequest
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
        $rackId = $this->route('id');

        return array_merge(
            $this->companyRules(),
            [
                'warehouse_id' => ['sometimes', 'required', 'exists:warehouses,id'],
                'area_id'      => ['sometimes', 'required', 'exists:areas,id'],
                'name'         => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('racks')->where(function ($query) {
                        return $query
                            ->where('warehouse_id', $this->warehouse_id)
                            ->where('area_id', $this->area_id);
                    })->ignore($rackId),
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
                'area_id.exists' => 'The selected area does not exist.',
                'name.required'  => 'The name field is mandatory.',
                'name.unique'    => 'A rack with this name already exists in the selected area.',
                'status.in'      => 'Status must be 1 (Active) or 0 (Inactive).',
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
