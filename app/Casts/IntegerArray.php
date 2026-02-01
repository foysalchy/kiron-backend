<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class IntegerArray implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return [];
        }

        $decoded = json_decode($value, true);
        return $decoded ? array_map('intval', $decoded) : [];
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return json_encode([]);
        }

        return json_encode(array_map('intval', (array) $value));
    }
}
