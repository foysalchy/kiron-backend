<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{

    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'is_verified',
    ];

    protected $casts = [
        'mail_password' => 'encrypted',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
