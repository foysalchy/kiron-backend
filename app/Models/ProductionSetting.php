<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionSetting extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'company_id',
        'order_prefix',
        'bom_prefix',
        'plan_prefix',
        'auto_consume_on_start',
        'require_qc_before_completion',
        'allow_over_consumption',
        'default_raw_material_warehouse_id',
        'default_finished_goods_warehouse_id',
    ];

    protected $casts = [
        'auto_consume_on_start' => 'boolean',
        'require_qc_before_completion' => 'boolean',
        'allow_over_consumption' => 'boolean',
    ];

    public function defaultRawMaterialWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_raw_material_warehouse_id');
    }

    public function defaultFinishedGoodsWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_finished_goods_warehouse_id');
    }
}
