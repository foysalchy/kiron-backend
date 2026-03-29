<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreCompanyRequest, UpdateCompanyRequest};
use App\Services\CompanyService;
use App\Exceptions\ApiException;
use App\Services\CompanyDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $filters = [
            'status' => $request->query('status'),
            'business_type' => $request->query('business_type'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $companies = $this->companyService->getAllCompanies($filters, true);

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
}
