<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpSetting extends Model
{  
    use SoftDeletes,CompanyScoped;

    protected static bool $columnsChecked = false;

    protected $fillable = [
        'company_id',
        'frequency_minute',
        'order_limit',
        'sms_limit_minutes',
        'sms_limit_count',
        'order_limit_minutes',
        'order_limit_count',
        'honeypot_filter_enabled',
        'block_checkout_only',
        'status'
    ];

    protected $casts = [
        'frequency_minute'        => 'integer',
        'order_limit'             => 'integer',
        'sms_limit_minutes'       => 'integer',
        'sms_limit_count'         => 'integer',
        'order_limit_minutes'     => 'integer',
        'order_limit_count'       => 'integer',
        'honeypot_filter_enabled' => 'boolean',
        'block_checkout_only'     => 'boolean',
    ];

    protected $hidden = ['deleted_at'];

    /**
     * Ensure database columns exist safely on runtime.
     */
    public static function ensureColumnsExist(): void
    {
        if (self::$columnsChecked) {
            return;
        }
        self::$columnsChecked = true;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('ip_settings')) {
                \Illuminate\Support\Facades\Schema::table('ip_settings', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('ip_settings', 'sms_limit_minutes')) {
                        $table->integer('sms_limit_minutes')->default(5)->nullable()->after('order_limit');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('ip_settings', 'sms_limit_count')) {
                        $table->integer('sms_limit_count')->default(3)->nullable()->after('sms_limit_minutes');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('ip_settings', 'order_limit_minutes')) {
                        $table->integer('order_limit_minutes')->default(5)->nullable()->after('sms_limit_count');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('ip_settings', 'order_limit_count')) {
                        $table->integer('order_limit_count')->default(1)->nullable()->after('order_limit_minutes');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('ip_settings', 'honeypot_filter_enabled')) {
                        $table->boolean('honeypot_filter_enabled')->default(true)->after('order_limit_count');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('ip_settings', 'block_checkout_only')) {
                        $table->boolean('block_checkout_only')->default(true)->after('honeypot_filter_enabled');
                    }
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("IpSetting ensureColumnsExist check: " . $e->getMessage());
        }
    }

    //scoped
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
