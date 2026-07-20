<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasSlugCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LandingPage extends Model
{
    use SoftDeletes, CompanyScoped,HasSlugCache;

    protected $fillable = [
        'company_id',
        'template_id',
        'product_id',
        'domain',
        'name',
        'title',
        'short_description',
        'thumbnail',
        'video',
        'description',
        'regular_price',
        'discount_price',
        'slug',
        'pixel',
        'meta_access_token',
        'header_code',
        'phone_number',
        'instruction',
        'extras',
        'instruction_title',
        'img_paths',
        'status',
    ];
    protected $casts = [
        'extras' => 'array',
        'img_paths' => 'array',
    ];
    protected $hidden = ['deleted_at', 'meta_access_token'];


    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // Accessors
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail
            ? Storage::disk('r2')->url($this->thumbnail)
            : null;
        // return $this->thumbnail ? asset('storage/' . $this->thumbnail) : null;
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video
            ? Storage::disk('r2')->url($this->video)
            : null;
        // return $this->video ? asset('storage/' . $this->video) : null;
    }

    // Mutators
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $value ?: Str::slug($this->name);
    }

    // Boot method to auto-generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($landingPage) {
            if (empty($landingPage->slug)) {
                $landingPage->slug = Str::slug($landingPage->name);
            }

            // Ensure slug uniqueness
            $originalSlug = $landingPage->slug;
            $count = 1;

            while (static::where('slug', $landingPage->slug)->exists()) {
                $landingPage->slug = $originalSlug . '-' . $count;
                $count++;
            }
        });
    }
    public function getDiscountPercentageAttribute()
    {
        $regular = $this->regular_price;
        $sale = $this->discount_price;

        if ($regular > 0 && $regular > $sale) {
            return round((($regular - $sale) / $regular) * 100);
        }

        return 0;
    }

}
