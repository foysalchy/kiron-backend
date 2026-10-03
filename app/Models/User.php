<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\HasPackageLimits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasPackageLimits;
    public string $limitKey = 'user';
    protected $fillable = [
        'name',
        'company_id',
        'email',
        'phone',
        'alternative_phone',
        'profile',
        'status',
        'role',
        'is_super_admin',
        'is_primary',
        'password',
        'setup_complete',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_primary' => 'boolean',
            'setup_complete' => 'boolean',

        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function blog(): HasMany
    {
        return $this->hasMany(Blog::class);
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ActionLog::class)->orderBy('id', 'desc')->limit(10);
    }

    public function history(): HasMany
    {
        return $this->hasMany(UserLoginHistory::class)->orderBy('id', 'desc')->limit(10);
    }
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
    public function isActive(): bool
    {
        return $this->status === Status::Active->value;
    }
    public function isDraft(): bool
    {
        return $this->status === Status::Draft->value;
    }

    public function canAccessCompany(int $companyId): bool
    {
        // Super admin can access any company
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->company_id == $companyId;
    }
}
