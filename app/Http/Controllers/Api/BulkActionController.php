<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BulkActionController extends Controller
{
    private array $resourceMap = [
        'parties'  => \App\Models\Party::class,
        'users'    => \App\Models\User::class,
        'products' => \App\Models\Product::class,
        'mega-categories' => \App\Models\MegaCategory::class,
        'sub-categories' => \App\Models\SubCategory::class,
        'mini-categories' => \App\Models\MiniCategory::class,
        'extra-categories' => \App\Models\ExtraCategory::class,
        'warehouses' => \App\Models\Warehouse::class,
        'areas' => \App\Models\Area::class,
        'cells' => \App\Models\Cell::class,
        'racks' => \App\Models\Rack::class,
        'bins' => \App\Models\Bin::class,
    ];

    private function resolveModel(string $resource): string
    {
        if (!isset($this->resourceMap[$resource])) {
            abort(404, "Resource '{$resource}' not found.");
        }

        return $this->resourceMap[$resource];
    }

    public function updateStatus(Request $request, string $resource)
    {
        $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer'],
            'status' => ['required', 'in:0,1'],
        ]);

        $model = $this->resolveModel($resource);

        $updated = $model::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        return response()->json([
            'message' => "Status updated successfully.",
            'updated' => $updated,
        ]);
    }

    public function bulkDelete(Request $request, string $resource)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        $model = $this->resolveModel($resource);

        $deleted = $model::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => "Deleted successfully.",
            'deleted' => $deleted,
        ]);
    }
}
