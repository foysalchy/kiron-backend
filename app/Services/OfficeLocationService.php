<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\OfficeLocation;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfficeLocationService
{
    /**
     * Get all office locations with optional pagination
     */
    public function getAllLocations(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = OfficeLocation::query();

            // Filter by status
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by location name or address
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('location_name', 'like', "%{$filters['search']}%")
                        ->orWhere('address', 'like', "%{$filters['search']}%")
                        ->orWhere('district', 'like', "%{$filters['search']}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching office locations: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch office locations');
        }
    }

    /**
     * Get office location by ID
     */
    public function getLocationById(int $id): OfficeLocation
    {
        $location = OfficeLocation::find($id);

        if (!$location) {
            throw ApiException::notFound('Office Location');
        }

        return $location;
    }

    /**
     * Create a new office location
     */
    public function createLocation(array $data): OfficeLocation
    {
        DB::beginTransaction();

        try {
            $location = OfficeLocation::create($data);
            LogHelper::created('office_location', $location->id, $location->company_id, $location->location_name);

            DB::commit();

            Log::info('Office Location created successfully', ['location_id' => $location->id]);

            return $location;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Office Location creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create office location');
        }
    }

    /**
     * Update office location
     */
    public function updateLocation(int $id, array $data): OfficeLocation
    {
        DB::beginTransaction();

        try {
            $location = $this->getLocationById($id);

            $location->update($data);
            LogHelper::updated('office_location', $location->id, $location->company_id, $location->location_name);

            DB::commit();

            Log::info('Office Location updated successfully', ['location_id' => $location->id]);

            return $location->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Office Location update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update office location');
        }
    }

    /**
     * Delete office location (soft delete)
     */
    public function deleteLocation(int $id): bool
    {
        DB::beginTransaction();
        try {
            $location = $this->getLocationById($id);
            $location->delete();
            LogHelper::deleted('office_location', $location->id, $location->company_id, $location->location_name);

            Log::info('Office Location deleted successfully', ['location_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Office Location deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete office location');
        }
    }

    /**
     * Restore soft deleted office location
     */
    public function restoreLocation(int $id): OfficeLocation
    {
        try {
            $location = OfficeLocation::withTrashed()->find($id);

            if (!$location) {
                throw ApiException::notFound('Office Location');
            }

            $location->restore();
            LogHelper::restored('office_location', $location->id, $location->company_id, $location->location_name);

            Log::info('Office Location restored successfully', ['location_id' => $id]);

            return $location;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Office Location restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore office location');
        }
    }

    /**
     * Permanently delete office location
     */
    public function forceDeleteLocation(int $id): bool
    {
        DB::beginTransaction();

        try {
            $location = OfficeLocation::withTrashed()->find($id);

            if (!$location) {
                throw ApiException::notFound('Office Location');
            }

            $location->forceDelete();
            LogHelper::forceDeleted('office_location', $location->id, $location->company_id, $location->location_name);

            DB::commit();

            Log::info('Office Location permanently deleted', ['location_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Office Location permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete office location');
        }
    }

    /**
     * Toggle office location status
     */
    public function toggleStatus(int $id): OfficeLocation
    {
        DB::beginTransaction();
        try {
            $location = $this->getLocationById($id);
            $currentStatus = Status::from($location->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $location->update([
                'status' => $newStatus->value
            ]);
            LogHelper::statusChanged('office_location', $location->id, $location->company_id, $location->location_name . ' new status ' . $newStatus->label());
            Log::info('Office Location status toggled', ['location_id' => $id]);
            DB::commit();
            return $location;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Office Location status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle office location status');
        }
    }
}
