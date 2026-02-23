<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLoginHistory extends Model
{ 
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'user_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
    ];
    protected $hidden = ['deleted_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
