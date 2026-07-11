<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\DisposalType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class DisposalTypeService
{
    /**
     * list disposal type
     */
    public function getAllTypes(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = DisposalType::query();

            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching disposal types: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch disposal types');
        }
    }
    /**
     * get id disposal type
     */

    public function getTypeById(int $id): DisposalType
    {
        $type = DisposalType::find($id);
        if (!$type) throw ApiException::notFound('disposal type');
        return $type;
    }
    /**
     * Created disposal type
     */

    public function createType(array $data): DisposalType
    {
        DB::beginTransaction();
        try {
            $data['status']=1;
            $type = DisposalType::create($data);
            LogHelper::created('disposal_type', $type->id, $type->company_id, $type->name);
            DB::commit();
            Log::info('Asset disposal type created successfully', ['disposal_type_id' => $type->id]);
            return $type;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to create disposal type');
        }
    }
    /**
     * Update disposal type
     */

    public function updateType(int $id, array $data): DisposalType
    {
        DB::beginTransaction();
        try {
            $type = $this->getTypeById($id);
            $type->update($data);
            LogHelper::updated('disposal_type', $type->id, $type->company_id, $type->name);
            DB::commit();
            Log::info('Asset disposal type updated successfully', ['disposal_type_id' => $type->id]);
            return $type->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        }catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to update disposal type');
        }
    }

    /**
     *  soft deleted disposal type
     */
    public function deleteType(int $id): bool
    {
        DB::beginTransaction();
        try {
            $type = $this->getTypeById($id);
            $type->delete();
            LogHelper::deleted('disposal_type', $type->id, $type->company_id, $type->name);
            DB::commit();
            Log::info('Asset disposal type deleted successfully', ['disposal_type_id' => $type->id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        }catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete disposal type');
        }
    }
    /**
     * Restore soft deleted disposal type
     */
    public function restoreType(int $id): DisposalType
    {
        DB::beginTransaction();
        try {
            $type = DisposalType::withTrashed()->find($id);
            if (!$type) {
                throw ApiException::notFound('Disposal type');
            }

            $type->restore();

            LogHelper::restored('disposal_type', $type->id, $type->company_id, $type->name);
            DB::commit();
            Log::info('Disposal type restored successfully', ['type_id' => $id]);

            return $type;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Disposal type restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore disposal type');
        }
    }

    /**
     * Permanently delete a disposal type
     */
    public function forceDeleteType(int $id): bool
    {
        DB::beginTransaction();
        try {
            $type = DisposalType::withTrashed()->find($id);
            if (!$type) {
                throw ApiException::notFound('Disposal type');
            }

            $type->forceDelete();

            LogHelper::forceDeleted('disposal_type', $id, $type->company_id, $type->name);
            DB::commit();
            Log::info('Disposal type permanently deleted successfully', ['type_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Disposal type permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete disposal type');
        }
    }
    /**
     * status disposal type
     */

    public function toggleStatus(int $id): DisposalType
    {
        DB::beginTransaction();
        try {
            $type = $this->getTypeById($id);
            $newStatus = Status::from($type->status) === Status::Active ? Status::Inactive : Status::Active;
            $type->update(['status' => $newStatus->value]);
            LogHelper::statusChanged('disposal_type', $type->id, $type->company_id, $type->name . ' to ' . $newStatus->label());
            DB::commit();
            Log::info('Asset disposal status toggled', ['disposal_type_id' => $id, 'new_status' => $newStatus->label()]);

            return $type;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        }
        catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
