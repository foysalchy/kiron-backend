<?php

namespace App\Rules;

use Illuminate\Validation\Rule;

class SlugRule
{
    public static function make(
        string $table,
        $ignoreId = null,
        $companyId = null
    ): array {
        $rule = Rule::unique($table, 'slug');

        //  company  unique
        if ($companyId) {
            $rule->where(function ($query) use ($companyId) {
                return $query->where('company_id', $companyId);
            });
        }

        //  update case ( ignore)
        if ($ignoreId) {
            $rule->ignore($ignoreId);
        }

        return [
            'required',
            'string',
            'max:255',
            $rule
        ];
    }
}