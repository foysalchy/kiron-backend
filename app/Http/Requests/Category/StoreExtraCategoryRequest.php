<?php

namespace App\Http\Requests\Category;

use App\Rules\SlugRule;
use Illuminate\Validation\Rule;

class StoreExtraCategoryRequest extends BaseCategoryRequest
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
                'sub_category_id'  => ['required', 'exists:sub_categories,id'],
                'mini_category_id' => ['required', 'exists:mini_categories,id'],
                'name'             => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('extra_categories')->where(function ($query) use ($companyId) {
                        return $query
                            ->where('company_id', $companyId)
                            ->where('mega_category_id', $this->mega_category_id)
                            ->where('sub_category_id', $this->sub_category_id)
                            ->where('mini_category_id', $this->mini_category_id);
                    }),
                ],
                'slug'             => SlugRule::make(
                    'extra_categories',
                    null,
                    $companyId
                ),
            ]
        );
    }
}