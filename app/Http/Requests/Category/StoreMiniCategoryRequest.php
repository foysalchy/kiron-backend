<?php

namespace App\Http\Requests\Category;


class StoreMiniCategoryRequest extends BaseCategoryRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'sub_category_id' => ['required', 'exists:sub_categories,id'],
        ]);
    }
}
