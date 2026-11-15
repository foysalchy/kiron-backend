<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\SearchProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchKeywordController extends Controller
{
    /**
     * Get list of search keywords with summary stats & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'keyword'    => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = SearchProduct::query();

        if ($request->filled('keyword')) {
            $query->where('keyword', 'LIKE', '%' . trim($request->keyword) . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Summary metrics across current filtered scope
        $totalSearches = (clone $query)->count();
        $uniqueKeywords = (clone $query)->distinct('keyword')->count('keyword');
        $topKeywordRow = (clone $query)
            ->select('keyword', DB::raw('count(*) as count'))
            ->groupBy('keyword')
            ->orderByDesc('count')
            ->first();

        $perPage = (int) ($request->get('per_page', 20));

        $keywords = $query->select(
            'keyword',
            DB::raw('max(id) as id'),
            DB::raw('count(*) as search_count'),
            DB::raw('max(created_at) as last_searched_at'),
            DB::raw('min(created_at) as first_searched_at')
        )
            ->groupBy('keyword')
            ->orderByDesc('search_count')
            ->paginate($perPage);

        $items = collect($keywords->items())->map(function ($row) {
            return [
                'id'               => (int) $row->id,
                'keyword'          => $row->keyword,
                'search_count'     => (int) $row->search_count,
                'last_searched_at' => $row->last_searched_at,
                'first_searched_at'=> $row->first_searched_at,
            ];
        });

        return ResponseHelper::success([
            'data'            => $items,
            'current_page'    => $keywords->currentPage(),
            'last_page'       => $keywords->lastPage(),
            'per_page'        => $keywords->perPage(),
            'total'           => $keywords->total(),
            'from'            => $keywords->firstItem() ?? 0,
            'to'              => $keywords->lastItem() ?? 0,
            'links'           => $keywords->linkCollection()->toArray(),
            'summary'         => [
                'total_searches'   => $totalSearches,
                'unique_keywords'  => $uniqueKeywords,
                'top_keyword'      => $topKeywordRow?->keyword ?? null,
                'top_keyword_count'=> (int) ($topKeywordRow?->count ?? 0),
            ],
        ]);
    }

    /**
     * Delete a search keyword (by ID or keyword string).
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if ($request->has('by_keyword') && $request->filled('keyword')) {
            SearchProduct::where('keyword', trim($request->keyword))->delete();
            return ResponseHelper::success(null, 'Search keyword deleted successfully.');
        }

        $record = SearchProduct::find($id);
        if (!$record) {
            if (!is_numeric($id)) {
                SearchProduct::where('keyword', $id)->delete();
                return ResponseHelper::success(null, 'Search keyword deleted successfully.');
            }
            return ResponseHelper::error('Search keyword not found.', 404);
        }

        // Delete all instances of this keyword
        SearchProduct::where('keyword', $record->keyword)->delete();

        return ResponseHelper::success(null, 'Search keyword deleted successfully.');
    }

    /**
     * Bulk delete search keywords.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids'       => ['nullable', 'array'],
            'ids.*'     => ['integer'],
            'keywords'  => ['nullable', 'array'],
            'keywords.*'=> ['string'],
        ]);

        $deletedCount = 0;

        if (!empty($request->keywords)) {
            $deletedCount += SearchProduct::whereIn('keyword', $request->keywords)->delete();
        }

        if (!empty($request->ids)) {
            $deletedCount += SearchProduct::whereIn('id', $request->ids)->delete();
        }

        return ResponseHelper::success([
            'deleted_count' => $deletedCount,
        ], 'Selected search keywords deleted successfully.');
    }
}
