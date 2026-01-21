<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class Bin extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'warehouse_id',
        'area_id',
        'rack_id',
        'cell_id',
        'bin_code',
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    // Relationships
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }

    public function cell()
    {
        return $this->belongsTo(Cell::class);
    }

    public function stockLedgers()
    {
        return $this->hasMany(ProductStockLedger::class);
    }

    public function sourceMovementItems()
    {
        return $this->hasMany(StockMovementItem::class, 'source_bin_id');
    }

    public function destinationMovementItems()
    {
        return $this->hasMany(StockMovementItem::class, 'destination_bin_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }
}
