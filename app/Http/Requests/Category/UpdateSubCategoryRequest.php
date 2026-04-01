<?php

namespace App\Http\Requests\Category;

use Illuminate\Validation\Rule;

class UpdateSubCategoryRequest extends BaseCategoryRequest
{
    protected function getCompanyId()
    {
        return $this->input('company_id') ?? $this->user()->company_id;
    }

    public function rules(): array
    {
        $subCategoryId = $this->route('id');
        $companyId     = $this->getCompanyId();

        return array_merge(
            $this->baseUpdateRules(),
            [
                'mega_category_id' => ['sometimes', 'required', 'exists:mega_categories,id'],
                'name'             => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('sub_categories')->where(function ($query) use ($companyId) {
                        return $query
                            ->where('company_id', $companyId)
                            ->where('mega_category_id', $this->mega_category_id);
                    })->ignore($subCategoryId),
                ],
            ]
        );
    }
}