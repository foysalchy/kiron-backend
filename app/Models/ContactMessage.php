<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'is_read'
    ];
    protected $hidden = [
        'deleted_at',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
