<?php

namespace App\Http\Requests\Category;

use App\Rules\SlugRule;

class StoreMegaCategoryRequest extends BaseCategoryRequest
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
                'slug' => SlugRule::make(
                    'mega_categories',
                    null,
                    $this->getCompanyId()
                ),
            ]
        );
    }
}
