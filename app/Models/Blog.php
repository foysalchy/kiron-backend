<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Blog extends Model
{
    use SoftDeletes, CompanyScoped;


    protected $fillable = [
        'user_id',
        'company_id',
        'title',
        'slug',
        'short',
        'body',
        'body_2',
        'body_3',
        'images',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
    ];
    protected $casts = [
        'images' => 'array',
        'meta_keywords' => 'array',
    ];


    protected $hidden = ['deleted_at'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Accessors
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail
            ? Storage::disk('r2')->url($this->thumbnail)
            : null;
    }
    //read time
    public function getReadingTimeAttribute()
    {
        $fullContent = $this->body . ' ' . $this->body_2 . ' ' . $this->body_3;

        $text = strip_tags($fullContent);
        $wordCount = preg_match_all('/\p{L}+/u', $text, $matches);
        $minutes = ceil($wordCount / 200);

        return $minutes > 0 ? $minutes : 1;
    }
}
