<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueProductIds implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $productIds = collect($value)->pluck('product_id');
        
        if ($productIds->count() !== $productIds->unique()->count()) {
            $fail('The same product cannot be added multiple times.');
        }
    }
}