<?php

namespace App\Http\Requests\Category;


class UpdateExtraCategoryRequest extends BaseCategoryRequest
{
    public function rules(): array
    {
        return array_merge($this->baseUpdateRules(), [
            'mini_category_id' => ['sometimes', 'required', 'exists:mini_categories,id'],
            'sub_category_id' => ['sometimes', 'required', 'exists:sub_categories,id'],
            'mega_category_id' => ['sometimes', 'required', 'exists:mega_categories,id'],
        ]);
    }
}
