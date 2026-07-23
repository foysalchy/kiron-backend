<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryAudit extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'audit_number',
        'audit_date',
        'audit_type',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'status' => 'integer',
    ];

    protected $appends = [
        'status_label',
        'status_color',
        'audit_type_label',
    ];


    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryAuditItem::class);
    }

    /**
     * Status helpers
     */
    public function isPending(): bool
    {
        return $this->status === Status::Pending->value;
    }

    public function isProcessing(): bool
    {
        return $this->status === Status::Processing->value;
    }

    public function isCompleted(): bool
    {
        return $this->status === Status::Completed->value;
    }

    public function isCancelled(): bool
    {
        return $this->status === Status::Cancelled->value;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            Status::Pending->value => 'Pending',
            Status::Processing->value => 'In Progress',
            Status::Completed->value => 'Completed',
            Status::Cancelled->value => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            Status::Pending->value => 'bg-gray-100 text-gray-700',
            Status::Processing->value => 'bg-blue-100 text-blue-700',
            Status::Completed->value => 'bg-green-100 text-green-700',
            Status::Cancelled->value => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Get audit type label
     */
    public function getAuditTypeLabelAttribute(): string
    {
        return match ($this->audit_type) {
            'full' => 'Full Warehouse',
            'partial' => 'Partial',
            'cycle' => 'Cycle Count',
            default => ucfirst($this->audit_type),
        };
    }

    /**
     * Scope: Pending audits
     */
    public function scopePending($query)
    {
        return $query->where('status', Status::Pending->value);
    }

    /**
     * Scope: In progress audits
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', Status::Processing->value);
    }

    /**
     * Scope: Completed audits
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', Status::Completed->value);
    }
}
