<?php

namespace App\Http\Requests\Category;


class StoreSubCategoryRequest extends BaseCategoryRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'mega_category_id' => ['required', 'exists:mega_categories,id'],
        ]);
    }
}
