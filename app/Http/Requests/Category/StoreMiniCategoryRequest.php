<?php

namespace App\Http\Requests\Category;

use App\Rules\SlugRule;
use Illuminate\Validation\Rule;

class StoreMiniCategoryRequest extends BaseCategoryRequest
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
                'mega_category_id' => ['nullable', 'exists:mega_categories,id'],
                'sub_category_id'  => ['nullable', 'exists:sub_categories,id'],
                'name'             => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('mini_categories')->where(function ($query) use ($companyId) {
                        return $query
                            ->where('company_id', $companyId)
                            ->where('mega_category_id', $this->mega_category_id)
                            ->where('sub_category_id', $this->sub_category_id);
                    }),
                ],
                'slug'             => SlugRule::make(
                    'mini_categories',
                    null,
                    $companyId
                ),
            ]
        );
    }
}