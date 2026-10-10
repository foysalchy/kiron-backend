<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasGlobalLayoutCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ContentSetting extends Model
{
    use CompanyScoped, HasGlobalLayoutCache;
    protected $fillable = [
        'company_id',
        'page_type',
        'icon_url',
        'icon_file',
        'title',
        'subtitle',
        'text_content',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status'     => Status::class,
    ];

    /* ── Page type constants ── */
    const PAGE_PRODUCT  = 'product_page';
    const PAGE_PRODUCT_SUB  = 'product_page_sub';
    const PAGE_CHECKOUT = 'checkout_page';
    const PAGE_ALL      = 'all_page';
    const PAGE_CART     = 'cart_page';
    const FOOTER_BOTTOM_RIGHT = 'footer_bottom_right';
    const PRIVACY_POLICY = 'privacy_policy';
    const TERMS_AND_CONDITIONS = 'terms_and_conditions';

    const PAGE_TYPES = [
        self::PAGE_PRODUCT,
        self::PAGE_PRODUCT_SUB,
        self::PAGE_CHECKOUT,
        self::PAGE_ALL,
        self::PAGE_CART,
        self::FOOTER_BOTTOM_RIGHT,
        self::PRIVACY_POLICY,
        self::TERMS_AND_CONDITIONS,
    ];

    public static function globalLayoutSections(): array
    {
        return ['footer_features', 'footer_bottom_right'];
    }

    /* ── Relations ── */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /* ── Scopes ── */
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByPage($query, string $pageType)
    {
        return $query->where('page_type', $pageType);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
    public function getIconUrlAttribute($value): ?string
    {
        return $this->icon_file
            ? Storage::disk('r2')->url($this->icon_file)
            : $value;
    }
}
