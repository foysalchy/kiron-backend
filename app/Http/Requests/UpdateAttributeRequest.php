<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseCompanyRequest;
use App\Models\AttributeValue;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAttributeRequest extends BaseCompanyRequest
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
                'attribute_group_id' => ['sometimes', 'required', 'exists:attribute_groups,id'],
                'name'               => ['sometimes', 'required', 'string', 'max:255'],
                'status'             => ['boolean'],
            ]
        );
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $attributeId = $this->route('id');

            // Update e 'name' na dile duplicate check-er dorkar nei
            if (!$this->filled('name')) {
                return;
            }

            $currentAttribute = AttributeValue::find($attributeId);

            if (!$currentAttribute) {
                return; // route model resolve na hole controller/route binding e already 404 handle hobe
            }

            $name = $this->input('name');

            // attribute_group_id request e na thakle existing record-er group_id use koro
            $groupId = $this->input('attribute_group_id', $currentAttribute->attribute_group_id);

            $exists = AttributeValue::where('attribute_group_id', $groupId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])
                ->where('id', '!=', $attributeId)
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', 'This attribute value already exists in this group.');
            }
        });
    }

    public function messages(): array
    {
        return array_merge(
            $this->companyMessages(),
            [
                'attribute_group_id.required' => 'Attribute group is required',
                'attribute_group_id.exists'   => 'Selected attribute group does not exist',
                'name.required'               => 'Attribute name is required',
            ]
        );
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