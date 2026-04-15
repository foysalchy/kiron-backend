<?php
namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialSetting extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'icon_name',
        'icon_image',
        'link',
        'hover_bg',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}