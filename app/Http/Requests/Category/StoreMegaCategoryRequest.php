<?php

namespace App\Http\Requests\Category;


class StoreMegaCategoryRequest extends BaseCategoryRequest
{
    public function rules(): array
    {
        return $this->baseRules();
    }
}