<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountType extends Model
{
    protected $fillable = ['name'];

    public function accountGroup(): HasMany
    {
        return $this->hasMany(AccountGroup::class);
    }
}
