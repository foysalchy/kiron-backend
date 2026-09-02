<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskSetting extends Model
{
    use HasFactory;

    protected $table = 'risk_settings';

    protected $fillable = [
        'company_id',
        'block_over_credit_orders',
        'block_negative_cash',
        'unusual_expense_threshold',
        'block_below_cost_sale',
        'lock_backdated_entries',
        'backdated_entry_lock_days',
        'high_return_threshold_pct',
        'block_negative_stock',
        'dead_stock_threshold_days',
    ];

    protected $casts = [
        'block_over_credit_orders'  => 'boolean',
        'block_negative_cash'       => 'boolean',
        'unusual_expense_threshold' => 'decimal:2',
        'block_below_cost_sale'     => 'boolean',
        'lock_backdated_entries'    => 'boolean',
        'backdated_entry_lock_days' => 'integer',
        'high_return_threshold_pct' => 'integer',
        'block_negative_stock'      => 'boolean',
        'dead_stock_threshold_days' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
