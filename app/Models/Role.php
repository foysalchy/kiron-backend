<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'name',

    ];
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }
}
