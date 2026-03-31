<?php

namespace App\Http\Requests\Category;

use App\Rules\SlugRule;

class StoreMiniCategoryRequest extends BaseCategoryRequest
{
    protected function getCompanyId()
    {
        return $this->input('company_id') ?? $this->user()->company_id;
    }

    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            [
                'mega_category_id' => ['nullable', 'exists:mega_categories,id'],
                'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
                'slug' => SlugRule::make(
                    'mini_categories',
                    null,
                    $this->getCompanyId()
                ),
            ]
        );
    }
}
       