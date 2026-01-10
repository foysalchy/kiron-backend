<?php

namespace App\Http\Requests\Category;


class StoreExtraCategoryRequest extends BaseCategoryRequest
{


    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'mini_category_id' => ['required', 'exists:mini_categories,id'],
        ]);
    }
}
