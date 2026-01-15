<?php
namespace App\Models;

use App\Models\Company;
use App\Models\RequisitionDetail;
use App\Models\User;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

// Requisition Model
class Requisition extends Model
{
    use HasFactory, SoftDeletes, CompanyScoped;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_COMPLETED = 3;

    protected $fillable = [
        'company_id',
        'user_id',
        'requisition_number',
        'request_date',
        'need_date',
        'total_amount',
        'status',
        'note',
        'reject_reason',
    ];

    protected $casts = [
        'request_date' => 'date',
        'need_date' => 'date',
        'total_amount' => 'decimal:2',
        'status' => 'integer',
    ];

    protected $appends = [
        'status_text',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($requisition) {
            if (empty($requisition->requisition_number)) {
                $requisition->requisition_number = self::generateRequisitionNumber();
            }
            
            if (empty($requisition->user_id)) {
                $requisition->user_id = Auth::id();
            }
        });
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select('id','name');
    }

    public function requisitionDetails(): HasMany
    {
        return $this->hasMany(RequisitionDetail::class);
    }

    // Scopes
    public function scopeByStatus($query, int $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Accessors
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_COMPLETED => 'Completed',
            default => 'Unknown',
        };
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function canEdit(): bool
    {
        return $this->isPending();
    }

    public function canApprove(): bool
    {
        return $this->isPending();
    }

    /**
     * Generate requisition number
     */
    public static function generateRequisitionNumber(): string
    {
        $prefix = 'REQ';
        $date = now()->format('Ymd');
        
        // Get last requisition number for today
        $lastRequisition = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastRequisition) {
            // Extract sequence number from last requisition
            $lastNumber = (int) substr($lastRequisition->requisition_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . '-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        // Example: REQ-20260115-0001
    }
}
