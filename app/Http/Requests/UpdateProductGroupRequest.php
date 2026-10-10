<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseCompanyRequest;
use App\Rules\SlugRule;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductGroupRequest extends BaseCompanyRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function getCompanyId()
    {
        return $this->input('company_id') ?? $this->user()->company_id;
    }

    public function rules(): array
    {
        $companyId = $this->getCompanyId();

        return array_merge($this->companyRules(), [
            'name' => 'required|string|max:255',
            'slug' => SlugRule::make(
                'product_groups',
                $this->route('id'),
                $companyId
            ),
            'filter_type' => 'required',
            'filter_parameters' => 'nullable|array',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
        ]);
    }
    public function messages(): array
    {
        return array_merge($this->companyMessages(), [
            'name.required'   => 'Group name is required',
            'filter_type.required' => 'Filter Type  is required',

        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
