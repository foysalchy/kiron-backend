<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerReview extends Model
{
     use HasFactory, SoftDeletes;

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

    protected $hidden = ['deleted_at'];
}
