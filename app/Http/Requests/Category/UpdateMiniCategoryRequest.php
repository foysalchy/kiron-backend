<?php

namespace App\Http\Requests\Category;



class UpdateMiniCategoryRequest extends BaseCategoryRequest
{
    public function rules(): array
    {
        return array_merge($this->baseUpdateRules(), [
            'mega_category_id' => ['sometimes', 'required', 'exists:mega_categories,id'],
            'sub_category_id' => ['sometimes', 'required', 'exists:sub_categories,id'],
        ]);
    }
}
