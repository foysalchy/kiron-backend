<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NoteTemplate extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'status'
    ];
    protected $hidden = ['deleted_at'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
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
}
