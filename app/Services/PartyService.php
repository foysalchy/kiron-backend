<?php

namespace App\Services;

use App\Models\Party;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartyService
{
    /**
     * Get all parties with optional pagination
     */
    public function getAllParties(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Party::query();

            // Filter by type
            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            // Filter by status
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }



            // Search
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%")
                        ->orWhere('phone', 'like', "%{$filters['search']}%");
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
            Log::error('Error fetching parties: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch parties');
        }
    }

    /**
     * Get party by ID
     */
    public function getPartyById(int $id): Party
    {
        $party = Party::find($id);

        if (!$party) {
            throw ApiException::notFound('Party');
        }

        return $party;
    }

    /**
     * Create a new party
     */
    public function createParty(array $data): Party
    {
        DB::beginTransaction();

        try {
            // Handle profile upload
            if (isset($data['profile'])) {
                $data['profile'] = FileUploadHelper::uploadImage(
                    $data['profile'],
                    'parties/profiles',
                    'public',
                    2048
                );
            }

            $party = Party::create($data);
            LogHelper::created('party', $party->id, $party->company_id);

            DB::commit();

            Log::info('Party created successfully', ['party_id' => $party->id]);

            return $party;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['profile'])) {
                FileUploadHelper::delete($data['profile']);
            }

            Log::error('Party creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to create party');
        }
    }

    /**
     * Update party
     */
    public function updateParty(int $id, array $data): Party
    {
        DB::beginTransaction();

        try {
            $party = $this->getPartyById($id);

            // Handle profile upload
            if (isset($data['profile'])) {
                $data['profile'] = FileUploadHelper::replace(
                    $data['profile'],
                    $party->profile,
                    'parties/profiles'
                );
            }

            $party->update($data);
            LogHelper::updated('party', $party->id, $party->company_id);

            DB::commit();

            Log::info('Party updated successfully', ['party_id' => $party->id]);

            return $party->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['profile'])) {
                FileUploadHelper::delete($data['profile']);
            }

            Log::error('Party update failed: ' . $e->getMessage(), [
                'party_id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to update party');
        }
    }

    /**
     * Delete party (soft delete)
     */
    public function deleteParty(int $id): bool
    {
        try {
            $party = $this->getPartyById($id);

            $party->delete();
            LogHelper::deleted('party', $party->id, $party->company_id);

            Log::info('Party deleted successfully', ['party_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Party deletion failed: ' . $e->getMessage(), [
                'party_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to delete party');
        }
    }

    /**
     * Restore soft deleted party
     */
    public function restoreParty(int $id): Party
    {
        try {
            $party = Party::withTrashed()->find($id);

            if (!$party) {
                throw ApiException::notFound('Party');
            }

            $party->restore();
            LogHelper::restored('party', $party->id, $party->company_id);

            Log::info('Party restored successfully', ['party_id' => $id]);

            return $party;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Party restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore party');
        }
    }

    /**
     * Permanently delete party
     */
    public function forceDeleteParty(int $id): bool
    {
        DB::beginTransaction();

        try {
            $party = Party::withTrashed()->find($id);

            if (!$party) {
                throw ApiException::notFound('Party');
            }

            FileUploadHelper::delete($party->profile);

            $party->forceDelete();
            LogHelper::forceDeleted('party', $party->id, $party->company_id);

            DB::commit();

            Log::info('Party permanently deleted', ['party_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Party permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete party');
        }
    }

    /**
     * Toggle party status
     */
    public function toggleStatus(int $id): Party
    {
        try {
            $party = $this->getPartyById($id);
            $party->update(['status' => !$party->status]);
            LogHelper::statusChanged('party', $party->id, $party->company_id);

            Log::info('Party status toggled', [
                'party_id' => $id,
                'new_status' => $party->status
            ]);

            return $party;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Party status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle party status');
        }
    }

    /**
     * Update party balance
     */
    public function updateBalance(int $id,  array $data): Party
    {
        DB::beginTransaction();

        try {
            $party = $this->getPartyById($id);

            if ($data['type'] === 'add') {
                $party->balance += $data['amount'];
            } elseif ($data['type'] === 'subtract') {
                $party->balance -=  $data['amount'];
            } elseif ($data['type'] === 'set') {
                $party->balance =  $data['amount'];
            }

            $party->save();
            LogHelper::custom('balance_updated', 'party', $id, $party->company_id);

            DB::commit();

            Log::info('Party balance updated', [
                'party_id' => $id,
                'new_balance' => $party->balance
            ]);

            return $party->load('company');
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Balance update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update balance');
        }
    }


    public function getSuppliers(): Collection
    {
        return Party::suppliers()->active()->get();
    }


    public function getCustomers(): Collection
    {
        return Party::customers()->active()->get();
    }

    public function searchParties(string $term, ?int $type = null): Collection
    {
        $query = Party::where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });



        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }
}
