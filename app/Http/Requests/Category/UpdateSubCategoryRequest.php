<?php

namespace App\Http\Requests\Category;


class UpdateSubCategoryRequest extends BaseCategoryRequest
{
    public function rules(): array
    {
        return array_merge($this->baseUpdateRules(), [
            'mega_category_id' => ['sometimes', 'required', 'exists:mega_categories,id'],
        ]);
    }
}
