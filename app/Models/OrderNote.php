<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderNote extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'order_id',
        'note',
        'type',
        'status'
    ];
    protected $hidden = ['deleted_at'];

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the company that owns the note
     */
    public function company() : BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
