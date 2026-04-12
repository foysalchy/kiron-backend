<?php 
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Courier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class CourierService
{
    /**
     * Get all couriers with optional pagination and filters
     */
    public function getAllCouriers(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Courier::query()->with(['method']); 

            // Filter by Status (including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Filter by Courier Method
            if (!empty($filters['name'])) {
                $query->where('name', $filters['name']);
            }

            // Search by contact name, phone or location
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('contact_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching couriers: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch couriers');
        }
    }

    /**
     * Get courier by ID
     */
    public function getCourierById(int $id): Courier
    {
        $courier = Courier::find($id);
        if (!$courier) {
            throw ApiException::notFound('Courier');
        }
        return $courier;
    }

    /**
     * Create a new courier
     */
    public function createCourier(array $data): Courier
    {
        DB::beginTransaction();
        try {
            $courier = Courier::create($data);
            
            LogHelper::created('courier', $courier->id, $courier->company_id, $courier->contact_name);
            
            DB::commit();
            Log::info('Courier created successfully', ['courier_id' => $courier->id]);
            return $courier;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create courier');
        }
    }

    /**
     * Update courier
     */
    public function updateCourier(int $id, array $data): Courier
    {
        DB::beginTransaction();
        try {
            $courier = $this->getCourierById($id);
            $courier->update($data);

            LogHelper::updated('courier', $courier->id, $courier->company_id, $courier->contact_name);
            
            DB::commit();
            Log::info('Courier Updated Successfully', ['courier_id' => $courier->id]);
            return $courier->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update courier');
        }
    }

    /**
     * Delete courier (Soft Delete)
     */
    public function deleteCourier(int $id): bool
    {
        DB::beginTransaction();
        try {
            $courier = $this->getCourierById($id);
            $courier->delete();

            LogHelper::deleted('courier', $courier->id, $courier->company_id, $courier->contact_name);
            
            DB::commit();
            Log::info('Courier Deleted Successfully', ['courier_id' => $id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete courier');
        }
    }

    /**
     * Restore courier
     */
    public function restoreCourier(int $id): Courier
    {
        DB::beginTransaction();
        try {
            $courier = Courier::withTrashed()->find($id);
            if (!$courier) {
                throw ApiException::notFound('Courier');
            }
            
            $courier->restore();
            
            LogHelper::restored('courier', $courier->id, $courier->company_id, $courier->contact_name);
            
            DB::commit();
            Log::info('Courier Restored Successfully', ['courier_id' => $id]);
            return $courier;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }

    /**
     * Permanently delete courier
     */
    public function forceDeleteCourier(int $id): bool
    {
        DB::beginTransaction();
        try {
            $courier = Courier::withTrashed()->find($id);

            if (!$courier) {
                throw ApiException::notFound('Courier');
            }

            $courier->forceDelete();
            
            LogHelper::forceDeleted('courier', $courier->id, $courier->company_id, $courier->contact_name);

            DB::commit();
            Log::info('Courier permanently deleted', ['courier_id' => $id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete courier');
        }
    }

    /**
     * Toggle courier status
     */
    public function toggleStatus(int $id): Courier
    {
        DB::beginTransaction();
        try {
            $courier = $this->getCourierById($id);
            $currentStatus = Status::from($courier->status);
            
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $courier->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('courier', $courier->id, $courier->company_id, $courier->contact_name . ' new status '.$newStatus->label());
            
            DB::commit();
            Log::info('Courier status toggled', ['courier_id' => $id, 'new_status' => $newStatus->value]);
            return $courier;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Courier status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle courier status');
        }
    }
}