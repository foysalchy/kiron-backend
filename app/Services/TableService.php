<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TableService
{
    public function getAllTables(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Table::query();

            if (isset($filters['is_active'])) {
                $query->where('is_active', $filters['is_active']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('table_number', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching tables: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch tables');
        }
    }

    public function getTableById(int $id): Table
    {
        $table = Table::find($id);
        if (!$table) {
            throw ApiException::notFound('table');
        }
        return $table;
    }

    public function createTable(array $data): Table
    {
        DB::beginTransaction();
        try {
            $table = Table::create($data);
            LogHelper::created('table', $table->id, $table->company_id, $table->table_number);
            DB::commit();
            Log::info('Table created successfully', ['table_id' => $table->id]);

            return $table;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Table creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create table');
        }
    }

    public function updateTable(int $id, array $data): Table
    {
        DB::beginTransaction();
        try {
            $table = $this->getTableById($id);

            $table->update($data);

            LogHelper::updated('table', $table->id, $table->company_id, $table->table_number);
            DB::commit();
            Log::info('Table Updated Successfully', ['table_id' => $table->id]);

            return $table->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Table update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update table');
        }
    }

    public function deleteTable(int $id): bool
    {
        DB::beginTransaction();
        try {
            $table = $this->getTableById($id);

            $table->delete();

            LogHelper::deleted('table', $table->id, $table->company_id, $table->table_number);
            DB::commit();
            Log::info('Table deleted successfully', ['table_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Table deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete table');
        }
    }

    public function toggleStatus(int $id): Table
    {
        DB::beginTransaction();
        try {
            $table = $this->getTableById($id);

            $newStatus = $table->is_active == 1 ? 0 : 1;

            $table->update([
                'is_active' => $newStatus
            ]);

            LogHelper::statusChanged('table', $table->id, $table->company_id, $table->table_number . ' new status ' . ($newStatus ? 'Active' : 'Inactive'));
            DB::commit();
            Log::info('Table status toggled', ['table_id' => $id, 'new_status' => $newStatus]);

            return $table;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Table status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle table status');
        }
    }
}

