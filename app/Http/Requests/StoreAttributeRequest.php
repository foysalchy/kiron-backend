<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use App\Models\AttributeValue;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class StoreAttributeRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->companyRules(),
            [
                'attribute_group_id' => ['required', 'exists:attribute_groups,id'],
                'names'              => ['required', 'array', 'min:1'],
                'names.*'            => ['required', 'string', 'max:255'],
                'status'             => ['boolean'],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'attribute_group_id.required' => 'Attribute group is required',
                'attribute_group_id.exists'   => 'Selected attribute group does not exist',
                'names.required'              => 'At least one attribute name is required',
                'names.*.required'            => 'Attribute name cannot be empty',
            ]
        );
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $names   = $this->input('names', []);
            $groupId = $this->input('attribute_group_id');

            if (empty($names) || !$groupId) {
                return;
            }

            $lowered = array_map(fn($n) => mb_strtolower(trim($n)), $names);

            // 1. Submitted array-er moddhei duplicate (case-insensitive) check
            $seen = [];
            foreach ($lowered as $index => $name) {
                if (isset($seen[$name])) {
                    $validator->errors()->add(
                        "names.$index",
                        "Duplicate attribute value '{$names[$index]}' in submitted list"
                    );
                }
                $seen[$name] = true;
            }

            // 2. Existing DB values-er against check (same group, case-insensitive)
            $existing = AttributeValue::where('attribute_group_id', $groupId)
                ->whereIn(DB::raw('LOWER(name)'), array_unique($lowered))
                ->pluck('name');

            if ($existing->isNotEmpty()) {
                $validator->errors()->add(
                    'names',
                    'These values already exist in this attribute group: ' . $existing->implode(', ')
                );
            }
        });
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
