<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreCompanyRequest, UpdateCompanyRequest};
use App\Services\CompanyService;
use App\Exceptions\ApiException;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\CompanyUpdateRequest;
use App\Models\ExtraOrderCharge;
use App\Models\PricingPackage;
use App\Services\CompanyDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function __construct(
        protected CompanyService $companyService,
        protected CompanyDeletionService $companyDelationService
    ) {}

    /**
     * Get company profile summary with user and order counts
     */
    public function getProfile(int $id): JsonResponse
    {
        $company = $this->companyService->getCompanyProfileById($id);

        return ResponseHelper::success($company, 'Company retrieved successfully');
    }

    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'search'              => $request->search,
            'status'              => $request->status,
            'business_type'       => $request->business_type,
            'pricing_package_id'  => $request->pricing_package_id,
            'registered_from'     => $request->registered_from,
            'registered_to'       => $request->registered_to,
            'expire_from'         => $request->expire_from,
            'expire_to'           => $request->expire_to,
            'free_trial'          => $request->free_trial,
            'expiring_in_days' => $request->expiring_in_days ? (int) $request->expiring_in_days : null,
            'sort_by'             => $request->sort_by,
            'sort_order'          => $request->sort_order,
            'per_page'            => $request->per_page,
        ], fn($v) => $v !== null && $v !== '');

        $companies = $this->companyService->getAllCompanies($filters);

        return ResponseHelper::success($companies, 'Companies retrieved successfully');
    }


    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->companyService->createCompany($request->validated());

        return ResponseHelper::created($company, 'Company created successfully');
    }

    public function show(int $id): JsonResponse
    {
        $company = $this->companyService->getCompanyById($id);

        return ResponseHelper::success($company, 'Company retrieved successfully');
    }


    public function update(UpdateCompanyRequest $request, int $id): JsonResponse
    {
        $company = $this->companyService->updateCompany($id, $request->validated());

        return ResponseHelper::success($company, 'Company updated successfully');
    }

    public function storeUpdateRequest(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'nullable|string|max:255',
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'note'  => 'nullable|string|max:500',
        ]);

        $company = $this->companyService->storeUpdateRequest($id, $data);

        return ResponseHelper::success($company, 'Company updated request successfully');
    }
    public function updateUpdateRequest(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'nullable|string|max:255',
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'note'  => 'nullable|string|max:500',
        ]);

        $company = $this->companyService->updateUpdateRequest($id,$data);

        return ResponseHelper::success($company, 'Company updated request successfully');
    }

    public function approveUpdateRequest(int $requestId): JsonResponse
    {
        DB::transaction(function () use ($requestId) {
            $updateRequest = CompanyUpdateRequest::with('company')->findOrFail($requestId);


            $fieldsToUpdate = collect([
                'logo'  => $updateRequest->logo,
                'name'  => $updateRequest->name,
                'email' => $updateRequest->email,
                'phone' => $updateRequest->phone,
            ])->filter(fn($value) => !is_null($value))->toArray();

            if (!empty($fieldsToUpdate)) {
                $updateRequest->company->update($fieldsToUpdate);
            }

            $updateRequest->update(['status' => Status::Approved->value]);
        });

        return response()->json(['message' => 'Request approved and company updated.']);
    }
    public function rejectUpdateRequest(int $requestId): JsonResponse
    {
        $updateRequest = CompanyUpdateRequest::findOrFail($requestId);



        $updateRequest->update(['status' => Status::Rejected->value]);

        return response()->json(['message' => 'Request rejected.']);
    }
    public function upgradeSubscription(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'pricing_package_id' => 'required|exists:pricing_packages,id',
            'billing_cycle'      => 'required|in:monthly,quarterly,yearly',
        ]);

        DB::transaction(function () use ($data, $id) {
            $company = Company::findOrFail($id);

            $package = PricingPackage::with([
                'tiers' => fn($q) =>
                $q->where('billing_cycle', $data['billing_cycle'])
            ])->findOrFail($data['pricing_package_id']);

            $tier = $package->tiers->first();
            if (!$tier) throw new \Exception("No tier for this billing cycle.");

            $amountPaid = $tier->discount_price > 0 && $tier->discount_price < $tier->regular_price
                ? $tier->discount_price
                : $tier->regular_price;

            $now    = Carbon::now();
            $endsAt = match ($data['billing_cycle']) {
                'yearly'    => $now->copy()->addYear(),
                'quarterly' => $now->copy()->addMonths(3),
                default     => $now->copy()->addMonth(),
            };


            $company->subscriptions()->update(['status' => Status::Inactive->value]);

            CompanySubscription::create([
                'company_id'         => $company->id,
                'pricing_package_id'  => $package->id,
                'billing_cycle'      => $data['billing_cycle'],
                'amount_paid'        => $amountPaid,
                'payment_method'     => 'manual',
                'payment_status'     => 'pending',
                'starts_at'          => $now,
                'ends_at'            => $endsAt,
                'status'             => Status::Active->value,
            ]);
            $company->pricing_package_id = $package->id;
            $company->update();
        });

        return response()->json(['message' => 'Package upgraded successfully.']);
    }
    public function destroy(int $id): JsonResponse
    {
        $this->companyDelationService->softDelete($id);

        return ResponseHelper::success(null, 'Company deleted successfully');
    }
    public function deletionSummary(int $id): JsonResponse
    {
        $data = $this->companyDelationService->getDeletionSummary($id);

        return ResponseHelper::success($data, 'Company deletion summary retrived successfully');
    }
    public function deleteUpdateRequest(int $id): JsonResponse
    {
        $data = CompanyUpdateRequest::findOrFail($id);
        if ($data->status == Status::Approved->value || $data->status == Status::Rejected->value) {
            throw ApiException::notFound('Cannot delete this request');
        }
        $data->delete();
        return ResponseHelper::success($data, 'Company update request deleted successfully');
    }
    public function canDeleteCompany(int $id): JsonResponse
    {
        $data =  $this->companyDelationService->canDelete($id);

        return ResponseHelper::success($data, 'Company deletion check');
    }


    public function restore(int $id): JsonResponse
    {
        $company = $this->companyDelationService->restore($id);

        return ResponseHelper::success($company, 'Company restored successfully');
    }


    public function forceDestroy(int $id): JsonResponse
    {
        $this->companyDelationService->forceDelete($id);

        return ResponseHelper::success(null, 'Company permanently deleted');
    }

    /**
     * Toggle company status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $company = $this->companyService->toggleStatus($id);

        return ResponseHelper::success($company, 'Company status updated successfully');
    }

    /**
     * Get companies by business type
     */
    public function getByBusinessType(string $type): JsonResponse
    {
        $companies = $this->companyService->getCompaniesByBusinessType($type);

        return ResponseHelper::success($companies, 'Companies retrieved successfully');
    }

    /**
     * Get active companies
     */
    public function getActiveCompanies(): JsonResponse
    {
        $companies = $this->companyService->getActiveCompanies();

        return ResponseHelper::success($companies, 'Active companies retrieved successfully');
    }

    /**
     * Search companies
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->query('term');

        if (!$term) {
            throw ApiException::badRequest('Search term is required');
        }

        $companies = $this->companyService->searchCompanies($term);

        return ResponseHelper::success($companies, 'Search results retrieved successfully');
    }
    public function extraCharges(Request $request, int $id): JsonResponse
    {
        $charges = ExtraOrderCharge::with('order:id,order_no')
            ->where('company_id', $id)
            ->whereBetween('month', [
                $request->start_month,
                $request->end_month,
            ])
            ->get();

        return response()->json(['data' => $charges]);
    }
}
