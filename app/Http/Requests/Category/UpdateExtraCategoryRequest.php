<?php

namespace App\Http\Requests\Category;


class UpdateExtraCategoryRequest extends BaseCategoryRequest
{
    public function rules(): array
    {
        return array_merge($this->baseUpdateRules(), [
            'mini_category_id' => ['sometimes', 'required', 'exists:mini_categories,id'],
        ]);
    }
}
