<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pricing extends Model
{
    use SoftDeletes;

        protected $fillable = [
            'name',
            'sub_title',
            'monthly_regular_price',
            'monthly_discount_price',
            'yearly_regular_price',
            'yearly_discount_price',
            'order_limitation',
            'user_limitation',
            'features',
            'is_featured',
            'free_trial',
            'status',
        ];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'features' => 'array',
    ];
    // Relationships

    // status scope
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }
    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }
}
