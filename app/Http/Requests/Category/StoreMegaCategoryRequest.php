<?php

namespace App\Http\Requests\Category;

use App\Rules\SlugRule;
use Illuminate\Validation\Rule;

class StoreMegaCategoryRequest extends BaseCategoryRequest
{
    protected function getCompanyId()
    {
        return $this->input('company_id') ?? $this->user()->company_id;
    }

    public function rules(): array
    {
        $companyId = $this->getCompanyId();

        return array_merge(
            $this->baseRules(),
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('mega_categories')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    }),
                ],
                'slug' => SlugRule::make(
                    'mega_categories',
                    null,
                    $companyId
                ),
            ]
        );
    }
}