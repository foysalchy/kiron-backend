<?php

namespace App\Http\Requests\Category;

use Illuminate\Validation\Rule;

class UpdateExtraCategoryRequest extends BaseCategoryRequest
{
    protected function getCompanyId()
    {
        return $this->input('company_id') ?? $this->user()->company_id;
    }

    public function rules(): array
    {
        $extraCategoryId = $this->route('id');
        $companyId       = $this->getCompanyId();

        return array_merge(
            $this->baseUpdateRules(),
            [
                'mega_category_id' => ['sometimes', 'required', 'exists:mega_categories,id'],
                'sub_category_id'  => ['sometimes', 'required', 'exists:sub_categories,id'],
                'mini_category_id' => ['sometimes', 'required', 'exists:mini_categories,id'],
                'name'             => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('extra_categories')->where(function ($query) use ($companyId) {
                        return $query
                            ->where('company_id', $companyId)
                            ->where('mega_category_id', $this->mega_category_id)
                            ->where('sub_category_id', $this->sub_category_id)
                            ->where('mini_category_id', $this->mini_category_id);
                    })->ignore($extraCategoryId),
                ],
            ]
        );
    }
}