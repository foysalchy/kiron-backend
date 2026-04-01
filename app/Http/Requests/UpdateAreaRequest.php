<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Http\Requests\UpdateBaseCompanyRequest;

class UpdateAreaRequest extends UpdateBaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $areaId = $this->route('id');

        return array_merge(
            $this->companyRules(),
            [
                'warehouse_id' => ['sometimes', 'required', 'exists:warehouses,id'],
                'name'         => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('areas')->where(function ($query) {
                        return $query->where('warehouse_id', $this->warehouse_id);
                    })->ignore($areaId),
                ],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'warehouse_id.exists' => 'The selected warehouse does not exist.',
                'name.required'       => 'The name field is mandatory.',
                'name.unique'         => 'An area with this name already exists in the selected warehouse.',
            ]
        );
    }

    public function failedValidation(Validator $validator)
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
