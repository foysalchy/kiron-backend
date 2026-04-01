<?php

namespace App\Http\Requests\Category;

use Illuminate\Validation\Rule;

class UpdateMegaCategoryRequest extends BaseCategoryRequest
{
    protected function getCompanyId()
    {
        return $this->input('company_id') ?? $this->user()->company_id;
    }

    public function rules(): array
    {
        $megaCategoryId = $this->route('id');
        $companyId      = $this->getCompanyId();

        return array_merge(
            $this->baseUpdateRules(),
            [
                'name' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('mega_categories')->where(function ($query) use ($companyId) {
                        return $query->where('company_id', $companyId);
                    })->ignore($megaCategoryId),
                ],
            ]
        );
    }
}
