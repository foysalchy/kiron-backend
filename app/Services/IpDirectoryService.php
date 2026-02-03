<?php 
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\IpDirectory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class IpDirectoryService
{ 
    /**
     * Get all IP directories with optional pagination
     */
    public function getAllIps(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = IpDirectory::query();

            // Filter by Status
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by IP Address
            if (!empty($filters['search'])) {
                $query->where('ip_address', 'like', "%{$filters['search']}%");
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching IP directory: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch IP list');
        }
    }
    /**
     * Get IP by ID
    */
    public function getIpById(int $id): IpDirectory
    {
        $ip = IpDirectory::find($id);
        if (!$ip) {
            throw ApiException::notFound('IP Address');
        }
        return $ip;
    }
   /**
     * Add a new IP to directory
     */
    public function createIp(array $data): IpDirectory
    {
        DB::beginTransaction();

        try {
            $ip = IpDirectory::create($data);
            
            LogHelper::created('ip_directory', $ip->id, $ip->company_id);

            DB::commit();
            Log::info('IP added successfully', ['ip_id' => $ip->id, 'ip_address' => $ip->ip_address]);

            return $ip;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP directory creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to add IP address');
        }
    }

    /**
     * Update IP address details
     */
    public function updateIp(int $id, array $data): IpDirectory
    {
        DB::beginTransaction();

        try {
            $ip = $this->getIpById($id);
            $ip->update($data);

            LogHelper::updated('ip_directory', $ip->id, $ip->company_id);

            DB::commit();
            Log::info('IP updated successfully', ['ip_id' => $ip->id]);

            return $ip->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update IP address');
        }
    }

    /**
     * Delete IP (Soft Delete)
     */
    public function deleteIp(int $id): bool
    {
        DB::beginTransaction();
        try {
            $ip = $this->getIpById($id);
            $ip->delete();

            LogHelper::deleted('ip_directory', $ip->id, $ip->company_id);

            DB::commit();
            Log::info('IP soft deleted successfully', ['ip_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete IP');
        }
    }

    /**
     * Restore soft deleted IP
     */
    public function restoreIp(int $id): IpDirectory
    {
        DB::beginTransaction();
        try {
            $ip = IpDirectory::withTrashed()->find($id);

            if (!$ip) {
                throw ApiException::notFound('IP Address');
            }

            $ip->restore();
            LogHelper::restored('ip_directory', $ip->id, $ip->company_id);

            DB::commit();
            Log::info('IP restored successfully', ['ip_id' => $id]);

            return $ip;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore IP');
        }
    }

    /**
     * Permanently delete IP
     */
    public function forceDeleteIp(int $id): bool
    {
        DB::beginTransaction();
        try {
            $ip = IpDirectory::withTrashed()->find($id);

            if (!$ip) {
                throw ApiException::notFound('IP Address');
            }

            $ip->forceDelete();
            LogHelper::forceDeleted('ip_directory', $id, $ip->company_id);

            DB::commit();
            Log::info('IP permanently deleted', ['ip_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete IP');
        }
    }

    /**
     * Toggle IP status (Active/Block)
     */
    public function toggleStatus(int $id): IpDirectory
    {
        DB::beginTransaction();
        try {
            $ip = $this->getIpById($id);

            $currentStatus = Status::from($ip->status);
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $ip->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged(
                'ip_directory',
                $ip->id,
                $ip->company_id,
                "IP {$ip->ip_address} status changed to " . $newStatus->label()
            );

            DB::commit();
            Log::info('IP status toggled', ['ip_id' => $id, 'new_status' => $newStatus->label()]);

            return $ip;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle IP status');
        }
    }
}