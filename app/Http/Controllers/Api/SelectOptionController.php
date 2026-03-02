<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\MegaCategory;
use App\Models\Party;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class SelectOptionController extends Controller
{
    /**
     * Get select options for various entities
     */
    public function warehouseOptions()
    {
        return Warehouse::select('id', 'name')->orderBy('name', 'asc')->get();
    }
    public function supplierOptions()
    {
        return Party::where('type', 1)->orderBy('name', 'asc')->get();
    }
    public function customersOptions()
    {
        return Party::where('type', 2)->orderBy('name', 'asc')->get();
    }
    public function productOptions()
    {
        return Product::with([
            'brand',
            'galleries',

            'variations.attributes.attributeGroup',
            'variations.attributes.attributeValue',
            'variations.stocks.warehouse',
        ])->orderBy('title', 'asc')->get();
    }

    public function purchaseOptions()
    {
        return Purchase::orderBy('purchase_date', 'desc')->get();
    }

    public function attributeGroupOptions()
    {
        return AttributeGroup::select('id', 'name')->orderBy('name', 'asc')->get();
    }
    public function megaCategoryOptions()
    {
        return MegaCategory::select('id', 'name')->orderBy('name', 'asc')->get();
    }
}
