<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Requisition;
class RequisitionDetail extends Model
{
     protected $fillable = [
        'requisition_id',
        'product_id',
        'unit',
        'quantity',
        'price',
        'amount',
    ];

    protected $casts = [

        'quantity' => 'integer',
        'price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function requisition(): BelongsTo
    
    {
        return $this->belongsTo(Requisition::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->select('id','title','thumbnail');
    }
}
