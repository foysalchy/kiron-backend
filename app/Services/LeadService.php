<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Party;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Hash, Log};

class LeadService
{
    /**
     * Get all Leads with filtering and pagination
     */
    public function getAllLeads(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Lead::query();

            if (!empty($filters['select'])) {
                $selectArray = is_string($filters['select']) ? explode(',', $filters['select']) : $filters['select'];
                $query->select($selectArray);
            }

            if (!empty($filters['with'])) {
                $query->with($filters['with']);
            }


            if (!empty($filters['select'])) {
                $selectArray = is_string($filters['select']) ? explode(',', $filters['select']) : $filters['select'];
                $query->select($selectArray);
            }

            if (!empty($filters['with'])) {
                $query->with($filters['with']);
            }


            if (isset($filters['status']) && $filters['status'] !== '') {
                if ($filters['status'] === 'trashed' || (int)$filters['status'] === Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['lead_source_id'])) {
                $query->where('lead_source_id', $filters['lead_source_id']);
            }

            if (!empty($filters['source_name'])) {
                $query->whereHas('leadSource', function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['source_name']}%");
                });
            }

            if (!empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('full_name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%")
                        ->orWhere('phone', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching leads with filters: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch leads');
        }
    }
    /**
     * Get Lead by ID
     */
    public function getLeadById(int $id): Lead
    {
        $lead = Lead::with(['leadSource', 'leadStatus'])->find($id);

        if (!$lead) {
            throw ApiException::notFound('Lead');
        }

        return $lead;
    }
    /**
     * Create a new Lead
     */
    public function createLead(array $data): Lead
    {
        DB::beginTransaction();
        try {
            $lead = Lead::create($data);

            LogHelper::created('lead', $lead->id, $lead->company_id, $lead->full_name);
            DB::commit();
            Log::info('Lead created successfully', ['id' => $lead->id]);

            return $lead;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create Lead');
        }
    }
    /**
     * Update Lead
     */
    public function updateLead(int $id, array $data): Lead
    {
        DB::beginTransaction();
        try {
            $lead = $this->getLeadById($id);
            $lead->update($data);

            LogHelper::updated('lead', $lead->id, $lead->company_id, $lead->full_name);
            DB::commit();

            return $lead->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update Lead');
        }
    }
    public function convertToSeller(array $data, int $id)
    {
        DB::beginTransaction();
        try {
            $lead = $this->getLeadById($id);

            // create company
            $company = Company::create($data);

            // get status
            $convertStatus = LeadStatus::where('name', 'Succeffully Converted')->first();

            if (!$convertStatus) {
                throw new \Exception('Lead status not found');
            }

            $lead->lead_status_id = $convertStatus->id;
            $lead->save();
            LogHelper::custom('convert_to_seller', 'lead', $id, $company->id, $lead->full_name . ' Converted Seller');

            DB::commit();
            return $lead;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Converted to seller failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to converted seller');
        }
    }
    public function convertToCustomer(array $data, int $id)
    {
        DB::beginTransaction();
        try {
            $lead = $this->getLeadById($id);

            // create company
            if (isset($data['profile'])) {
                $data['profile'] = FileUploadHelper::uploadImage(
                    $data['profile'],
                    'parties/profiles',

                );
            }
            $data['password'] = Hash::make($data['password']);
            $party = Party::create($data);


            // get status
            $convertStatus = LeadStatus::where('name', 'Succeffully Converted')->first();

            if (!$convertStatus) {
                throw new \Exception('Lead status not found');
            }

            $lead->lead_status_id = $convertStatus->id;
            $lead->save();
            LogHelper::custom('convert_to_customer', 'lead', $id, $party->company_id, $lead->full_name . ' Converted Customer');

            DB::commit();
            return $lead;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Converted to customer failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to converted customer');
        }
    }
    /**
     * Soft Delete Lead
     */
    public function deleteLead(int $id): bool
    {
        DB::beginTransaction();
        try {
            $lead = $this->getLeadById($id);
            $lead->delete();

            LogHelper::deleted('lead', $lead->id, $lead->company_id, $lead->full_name);
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete Lead');
        }
    }

    /**
     * Restore Lead
     */
    public function restoreLead(int $id): Lead
    {
        DB::beginTransaction();
        try {
            $lead = Lead::withTrashed()->find($id);
            if (!$lead) throw ApiException::notFound('Lead');

            $lead->restore();
            LogHelper::restored('lead', $lead->id, $lead->company_id, $lead->full_name);
            DB::commit();

            return $lead;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore Lead');
        }
    }
    /**
     * Permanently delete Lead (Force Delete)
     */
    public function forceDeleteLead(int $id): bool
    {
        DB::beginTransaction();
        try {
            $lead = Lead::withTrashed()->find($id);

            if (!$lead) {
                throw ApiException::notFound('Lead');
            }

            $leadName = $lead->full_name;
            $companyId = $lead->company_id;

            $lead->forceDelete();

            LogHelper::forceDeleted('lead', $id, $companyId, $leadName);
            DB::commit();

            Log::info('Lead permanently deleted', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete Lead');
        }
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): Lead
    {
        DB::beginTransaction();
        try {
            $lead = $this->getLeadById($id);
            $newStatus = $lead->status === Status::Active->value ? Status::Inactive->value : Status::Active->value;

            $lead->update(['status' => $newStatus]);

            LogHelper::statusChanged('lead', $lead->id, $lead->company_id, $lead->full_name . ' to ' . Status::from($newStatus)->name);
            DB::commit();

            return $lead;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}


