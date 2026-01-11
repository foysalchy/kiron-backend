<?php

namespace App\Http\Requests\Category;



class UpdateMiniCategoryRequest extends BaseCategoryRequest
{
    public function rules(): array
    {
        return array_merge($this->baseUpdateRules(), [
            'sub_category_id' => ['sometimes', 'required', 'exists:sub_categories,id'],
        ]);
    }
}
