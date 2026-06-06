<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\AccountGroup;
use App\Models\Area;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AttributeGroup;
use App\Models\Bin;
use App\Models\Cell;
use App\Models\ChartOfAccount;
use App\Models\CustomerGroup;
use App\Models\DisposalType;
use App\Models\Domain;
use App\Models\DomainSetup;
use App\Models\MegaCategory;
use App\Models\Party;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Rack;
use App\Models\Role;
use App\Models\SupportDepartment;
use App\Models\User;
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
    public function areaOptions($warehouseId)
    {
        return Area::where('warehouse_id', $warehouseId)
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function rackOptions($areaId)
    {
        return Rack::where('area_id', $areaId)
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
    }
    public function cellOptions($rackId)
    {
        return Cell::where('rack_id', $rackId)
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
    }
    public function binOptions($warehouseId)
    {
        return Bin::where('warehouse_id', $warehouseId)
            ->select('id', 'name', 'bin_code')
            ->orderBy('name', 'asc')
            ->get();
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
    public function getProductByWarehouse($warehouseId)
    {
        $products = Product::with([
            'brand',
            'galleries',
            'variations.attributes.attributeGroup',
            'variations.attributes.attributeValue',
            'variations.stocks' => function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            },
            'variations.stocks.warehouse',
        ])
            ->where(function ($q) use ($warehouseId) {
                $q->whereJsonContains('warehouse_info', [
                    'warehouse_id' => (string) $warehouseId
                ])
                    ->orWhereHas('variations.stocks', function ($stockQuery) use ($warehouseId) {
                        $stockQuery->where('warehouse_id', $warehouseId)
                            ->where('quantity', '>', 0);
                    });
            })
            ->orderBy('title', 'asc')
            ->get();

        $mappedProducts = $products->map(function ($product) use ($warehouseId) {
            $warehouseTotalStock = 0;

            if ($product->type === 'single' && is_array($product->warehouse_info)) {
                $filteredWarehouseInfo = collect($product->warehouse_info)
                    ->where('warehouse_id', (string) $warehouseId)
                    ->values();
                $warehouseTotalStock = $filteredWarehouseInfo->sum('quantity');

                $product->warehouse_info = $filteredWarehouseInfo->toArray();
            }

            if ($product->type === 'variation') {
                $product->variations->map(function ($variation) {
                    $variationStock = $variation->stocks->sum('quantity');

                    $variation->available_stock = $variationStock;
                    $variation->stock_quantity = $variationStock;

                    return $variation;
                });

                $warehouseTotalStock = $product->variations->sum('available_stock');
            }

            $product->available_stock = $warehouseTotalStock;
            $product->stock_quantity = $warehouseTotalStock;

            return $product;
        });

        return $mappedProducts->filter(function ($product) {
            return $product->available_stock > 0;
        })->values();
    }
    public function getProductByWarehouseAndBin($warehouseId, $binId)
    {
        $products = Product::with([
            'brand',
            'galleries',
            'variations.attributes.attributeGroup',
            'variations.attributes.attributeValue',
            // Eager load stock only for the specific warehouse AND bin
            'variations.stocks' => function ($q) use ($warehouseId, $binId) {
                $q->where('warehouse_id', $warehouseId)
                    ->where('bin_id', $binId);
            },
            'variations.stocks.warehouse',
            'variations.stocks.bin',
        ])
            ->where(function ($q) use ($warehouseId, $binId) {
                // For Single Products: Check JSON contains both warehouse_id and bin_id
                $q->whereJsonContains('warehouse_info', [
                    'warehouse_id' => (string) $warehouseId,
                    'bin_id' => (string) $binId
                ])
                    // For Variation Products: Check relation with both warehouse_id and bin_id
                    ->orWhereHas('variations.stocks', function ($stockQuery) use ($warehouseId, $binId) {
                        $stockQuery->where('warehouse_id', $warehouseId)
                            ->where('bin_id', $binId)
                            ->where('quantity', '>', 0);
                    });
            })
            ->orderBy('title', 'asc')
            ->get();

        $mappedProducts = $products->map(function ($product) use ($warehouseId, $binId) {
            $binTotalStock = 0;

            // Single Product Mapping
            if ($product->type === 'single' && is_array($product->warehouse_info)) {
                $filteredWarehouseInfo = collect($product->warehouse_info)
                    ->filter(function ($item) use ($warehouseId, $binId) {
                        return (string) $item['warehouse_id'] === (string) $warehouseId
                            && (string) ($item['bin_id'] ?? '') === (string) $binId;
                    })
                    ->values();

                $binTotalStock = $filteredWarehouseInfo->sum('quantity');
                $product->warehouse_info = $filteredWarehouseInfo->toArray();
            }

            // Variation Product Mapping
            if ($product->type === 'variation') {
                $product->variations->map(function ($variation) {
                    // Since stocks are already eager-loaded with bin_id condition, 
                    // this will only sum the stock for this specific bin
                    $variationStock = $variation->stocks->sum('quantity');

                    $variation->available_stock = $variationStock;
                    $variation->stock_quantity = $variationStock;

                    return $variation;
                });

                $binTotalStock = $product->variations->sum('available_stock');
            }

            // Set final stock at product level for this specific bin
            $product->available_stock = $binTotalStock;
            $product->stock_quantity = $binTotalStock;

            return $product;
        });

        // Remove products/variations that have 0 stock in this bin
        return $mappedProducts->filter(function ($product) {
            return $product->available_stock > 0;
        })->values();
    }
    public function userOptions()
    {
        return auth()->user()->company->users()->select('id', 'name')->orderBy('name', 'asc')->get();
    }
    public function superAdminUserOptions()
    {
        return User::where('is_super_admin', true)->select('id', 'name')->orderBy('name', 'asc')->get();
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
    public function nestedCategoryOptions()
    {
        $megaCategories = MegaCategory::select('id', 'name')
            ->where('status', 1)
            ->with([
                'subCategories' => function ($q) {
                    $q->select('id', 'mega_category_id', 'name')
                        ->where('status', 1)
                        ->orderBy('name')
                        ->with([
                            'miniCategories' => function ($q2) {
                                $q2->select('id', 'sub_category_id', 'name')
                                    ->where('status', 1)
                                    ->orderBy('name')
                                    ->with([
                                        'extraCategories' => function ($q3) {
                                            $q3->select('id', 'mini_category_id', 'name')
                                                ->where('status', 1)
                                                ->orderBy('name');
                                        }
                                    ]);
                            }
                        ]);
                }
            ])
            ->orderBy('name')
            ->get()
            ->map(fn($mega) => [
                'id'       => $mega->id,
                'name'     => $mega->name,
                'type'     => 'mega',
                'children' => $mega->subCategories->map(fn($sub) => [
                    'id'       => $sub->id,
                    'name'     => $sub->name,
                    'type'     => 'sub',
                    'children' => $sub->miniCategories->map(fn($mini) => [
                        'id'       => $mini->id,
                        'name'     => $mini->name,
                        'type'     => 'mini',
                        'children' => $mini->extraCategories->map(fn($extra) => [
                            'id'       => $extra->id,
                            'name'     => $extra->name,
                            'type'     => 'extra',
                            'children' => [],
                        ])->values(),
                    ])->values(),
                ])->values(),
            ]);

        return response()->json($megaCategories);
    }
    public function assetCategoryOptions()
    {
        return AssetCategory::select('id', 'name')->orderBy('name', 'asc')->get();
    }
    public function assetOptions()
    {
        return Asset::orderBy('name', 'asc')->get();
    }
    public function disposalTypeOptions()
    {
        return DisposalType::orderBy('name', 'asc')->get();
    }
    public function accountGroupOptions()
    {
        return AccountGroup::select('id', 'name')->orderBy('name', 'asc')->get();
    }
    public function accountExpenseOptions()
    {
        $groupIds = AccountGroup::where('account_type', 'Expense')
            ->pluck('id');

        $accounts = ChartOfAccount::whereIn('account_group_id', $groupIds)

            ->orderBy('name', 'asc')
            ->get();

        return $accounts;
    }
    public function incomeAccountOptions()
    {
        $groupIds = AccountGroup::where('account_type', 'Income')
            ->pluck('id');

        $accounts = ChartOfAccount::whereIn('account_group_id', $groupIds)

            ->orderBy('name', 'asc')
            ->get();

        return $accounts;
    }
    public function accountChartOptions()
    {
        $accounts = ChartOfAccount::orderBy('name', 'asc')->get();

        return $accounts;
    }
    public function supportDepartmentOptions()
    {
        $departments = SupportDepartment::orderBy('name', 'asc')->get();

        return $departments;
    }
    public function customerGroups()
    {
        $groups = CustomerGroup::orderBy('name', 'asc')->get();

        return $groups;
    }
    public function getRoles()
    {
        $roles = Role::orderBy('name', 'asc')->get();

        return $roles;
    }
    public function getAvailableDomains()
    {
        $setupDomains = DomainSetup::whereNotNull('custom_domain')
            ->pluck('custom_domain')
            ->toArray();

        $multiDomains = Domain::where('status', Status::Active->value)
            ->pluck('domain')
            ->toArray();

        $allDomains = array_unique(array_merge($setupDomains, $multiDomains));

        return response()->json([
            'data' => array_values($allDomains)
        ]);
    }
}
