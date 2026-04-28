<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'support_department_id',
        'user_id',
        'assigned_to',
        'subject',
        'description',
        'image',
        'status',
        'response_status'
    ];

    protected $hidden = ['deleted_at'];
    protected static function booted(): void
    {
        static::updating(function (SupportTicket $ticket) {
            if ($ticket->isDirty('status') && $ticket->status === Status::Closed->value) {
                $ticket->response_status = null;
            }
        });
    }


    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', Status::Pending->value);
    }
    public function scopeReplied($query)
    {
        return $query->where('status', Status::Replied->value);
    }
    public function scopeWaiting($query)
    {
        return $query->where('status', Status::Waiting->value);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', Status::Closed->value);
    }

    // Accessors
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function supportDepartment(): BelongsTo
    {
        return $this->belongsTo(SupportDepartment::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class);
    }
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
