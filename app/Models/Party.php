<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Party extends Authenticatable
{
    use HasFactory, SoftDeletes, CompanyScoped, Notifiable;

    // Type constants
    const TYPE_SUPPLIER = 1;
    const TYPE_CUSTOMER = 2;
    

    protected $fillable = [
        'company_id',
        'type',
        'name',
        'email',
        'phone',
        'alternative_phone',
        'gender',
        'division',
        'district',
        'thana',
        'address',
        'balance',
        'due_amount',
        'profile',
        'password',
        'status',
        'fb_psid',
        'ig_id',
        'ig_username'
    ];



    protected $hidden = [
        'deleted_at',
        'remember_token',
        'password',
    ];



    public function labels()
    {
        return $this->belongsToMany(Label::class, 'label_parties');
    }
    public function crmNotes()
    {
        return $this->hasMany(CrmNote::class)->with('agent')->latest();
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function logs(): HasMany
    {
        return $this->hasMany(ActionLog::class, 'action_id', 'id')
            ->where('module', 'party');
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'supplier_id', 'id');
    }
    // Scopes
    public function scopeSuppliers($query)
    {
        return $query->where('type', self::TYPE_SUPPLIER);
    }

    public function scopeCustomers($query)
    {
        return $query->where('type', self::TYPE_CUSTOMER);
    }

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


    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'customer_id');
    }
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'customer_id');
    }
    // Accessors
    public function getTypeTextAttribute(): string
    {
        return $this->type === self::TYPE_SUPPLIER ? 'Supplier' : 'Customer';
    }


    public function getProfileUrlAttribute(): ?string
    {
        return $this->profile ? asset('storage/' . $this->profile) : null;
    }

    // Helper methods
    public function isSupplier(): bool
    {
        return $this->type === self::TYPE_SUPPLIER;
    }

    public function isCustomer(): bool
    {
        return $this->type === self::TYPE_CUSTOMER;
    }
}
