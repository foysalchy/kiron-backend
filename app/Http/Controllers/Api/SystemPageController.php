<?php

namespace App\Http\Controllers\Api;

use App\Enums\SystemPageType;
use App\Http\Controllers\Controller;
use App\Models\SystemPage;
use Illuminate\Http\Request;

class SystemPageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->is_super_admin) {
            $pages = SystemPage::whereNull('company_id')
                ->whereIn('page_type', SystemPageType::superAdminScoped())
                ->get();
        } else {
            $pages = SystemPage::where('company_id', $user->company_id)
                ->whereIn('page_type', SystemPageType::companyScoped())
                ->get();
        }

        return response()->json([
            'message' => 'System pages fetched successfully',
            'data' => $pages,
        ]);
    }


    public function show(Request $request, string $pageType)
    {
        $page = $this->resolvePage($request, $pageType, forWrite: false);

        return response()->json([
            'message' => 'System page fetched successfully',
            'data' => $page,
        ]);
    }

    public function update(Request $request, string $pageType)
    {
        $validated = $request->validate([
            'title'            => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords'    => 'nullable|array',
        ]);

        $page = $this->resolvePage($request, $pageType, forWrite: true);
        $page->update($validated);

        return response()->json([
            'message' => 'System page updated successfully',
            'data' => $page,
        ]);
    }

    protected function resolvePage(Request $request, string $pageType, bool $forWrite)
    {
        $user = $request->user();

        if ($user->is_super_admin) {
            abort_unless(
                in_array($pageType, SystemPageType::superAdminScoped()),
                403,
                'Unauthorized'
            );

            return SystemPage::firstOrCreate([
                'company_id' => null,
                'page_type'  => $pageType,
            ]);
        }

        abort_unless(
            in_array($pageType, SystemPageType::companyScoped()),
            403,
            'Unauthorized'
        );

        return SystemPage::firstOrCreate([
            'company_id' => $user->company_id,
            'page_type'  => $pageType,
        ]);
    }
}
