<?php

namespace App\Http\Requests\Category;

use App\Rules\SlugRule;
use Illuminate\Validation\Rule;

class StoreSubCategoryRequest extends BaseCategoryRequest
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
                'mega_category_id' => ['required', 'exists:mega_categories,id'],
                'name'             => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('sub_categories')->where(function ($query) use ($companyId) {
                        return $query
                            ->where('company_id', $companyId)
                            ->where('mega_category_id', $this->mega_category_id);
                    }),
                ],
                'slug'             => SlugRule::make(
                    'sub_categories',
                    null,
                    $companyId
                ),
            ]
        );
    }
}