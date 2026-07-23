<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\HasSaasCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerReview extends Model
{
    use HasFactory, SoftDeletes,HasSaasCache;

    protected $fillable = [
        'name',
        'designation',
        'rating',
        'review',
        'status'
    ];

    protected $casts = [
        'status' => Status::class,
        'rating' => 'integer',
    ];
    public static function saasCacheKeys(): array
    {
        return ['saas_home_review_stats', 'saas_home_all_reviews'];
    }

    protected $hidden = ['deleted_at'];
}
