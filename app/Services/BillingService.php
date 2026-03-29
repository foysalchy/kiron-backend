<?php
namespace App\Services;

use App\Models\Billing;
use Illuminate\Pagination\LengthAwarePaginator;

class BillingService
{
    public function getBillingReports(int $companyId, array $filters): LengthAwarePaginator
    {
        $query = Billing::where('company_id', $companyId);

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $query->whereBetween('start_date', [$filters['from_date'], $filters['to_date']]);
        }

        if (!empty($filters['period'])) {
            $query->where('period', $filters['period']);
        }

        return $query->latest()->paginate(12);
    }
}
