<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    use HasFactory, CompanyScoped;

    protected $fillable = [
        'company_id',
        'default_provider',
        'openai_status',
        'openai_key',
        'openai_model',
        'openai_instructions',
        'gemini_status',
        'gemini_key',
        'gemini_model',
        'gemini_instructions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'openai_status' => 'boolean',
        'gemini_status' => 'boolean',
        'openai_key' => 'encrypted',
        'gemini_key' => 'encrypted',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
