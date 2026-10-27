<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Filter;
use App\Models\FilterValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FilterService
{
    public function getAllFilters(array $filters = [], bool $paginate = true, array $columns = ['*']): Collection|LengthAwarePaginator
    {
        try {
            $query = Filter::with(['values' => function ($q) {
                $q->select('id', 'filter_id', 'name', 'status');
            }]);

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (isset($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15, $columns)
                : $query->get($columns);
        } catch (\Exception $e) {
            Log::error('Error fetching filters: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch filters');
        }
    }

    public function getFilterById(int $id): Filter
    {
        $filter = Filter::with('values')->find($id);

        if (!$filter) {
            throw ApiException::notFound('Filter');
        }

        return $filter;
    }

    public function createFilter(array $data): Filter
    {
        try {
            return DB::transaction(function () use ($data) {
                $values = $data['values'] ?? [];
                unset($data['values']);

                $filter = Filter::create($data);

                if (!empty($values) && is_array($values)) {
                    foreach ($values as $val) {
                        $valName = trim((string) $val);
                        if ($valName !== '') {
                            FilterValue::create([
                                'company_id' => $filter->company_id,
                                'filter_id' => $filter->id,
                                'name' => $valName,
                                'status' => Status::Active->value,
                            ]);
                        }
                    }
                }

                LogHelper::created('filter', $filter->id, $filter->company_id, $filter->name);
                Log::info('Filter created successfully', ['filter_id' => $filter->id]);

                return $filter->load('values');
            });
        } catch (\Exception $e) {
            Log::error('Filter creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            throw ApiException::serverError('Failed to create Filter');
        }
    }

    public function updateFilter(int $id, array $data): Filter
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $filter = $this->getFilterById($id);

                $values = $data['values'] ?? null;
                unset($data['values']);

                $filter->update($data);

                if ($values !== null && is_array($values)) {
                    $cleanedValues = array_values(array_filter(array_map('trim', $values), fn($v) => $v !== ''));

                    $filter->values()->forceDelete();

                    foreach ($cleanedValues as $valName) {
                        FilterValue::create([
                            'company_id' => $filter->company_id,
                            'filter_id' => $filter->id,
                            'name' => $valName,
                            'status' => Status::Active->value,
                        ]);
                    }
                }

                LogHelper::updated('filter', $filter->id, $filter->company_id, $filter->name);
                Log::info('Filter updated successfully', ['filter_id' => $filter->id]);

                return $filter->fresh()->load('values');
            });
        } catch (\Exception $e) {
            Log::error('Filter update failed: ' . $e->getMessage(), [
                'filter_id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            throw ApiException::serverError('Failed to update Filter');
        }
    }

    public function deleteFilter(int $id): bool
    {
        try {
            $filter = $this->getFilterById($id);
            $filter->delete();

            Log::info('Filter deleted successfully', ['filter_id' => $id]);
            LogHelper::deleted('filter', $filter->id, $filter->company_id, $filter->name);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Filter deletion failed: ' . $e->getMessage(), [
                'filter_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            throw ApiException::serverError('Failed to delete Filter');
        }
    }

    public function restoreFilter(int $id): Filter
    {
        try {
            $filter = Filter::onlyTrashed()->find($id);

            if (!$filter) {
                throw ApiException::notFound('Filter');
            }

            $filter->restore();

            return $filter;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Filter restore failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore Filter');
        }
    }

    public function forceDeleteFilter(int $id): bool
    {
        try {
            $filter = Filter::onlyTrashed()->find($id);

            if (!$filter) {
                throw ApiException::notFound('Filter');
            }

            $filter->values()->forceDelete();
            $filter->forceDelete();

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Filter force deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to force delete Filter');
        }
    }

    public function toggleStatus(int $id): Filter
    {
        try {
            $filter = $this->getFilterById($id);
            $filter->status = $filter->status === Status::Active->value ? Status::Inactive->value : Status::Active->value;
            $filter->save();

            return $filter;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Filter status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle Filter status');
        }
    }

    public function addFilterValues(int $id, array $data): array
    {
        try {
            $filter = $this->getFilterById($id);
            $createdValues = [];

            DB::transaction(function () use ($filter, $data, &$createdValues) {
                $names = $data['names'] ?? [];
                if (is_string($names)) {
                    $names = [$names];
                }

                foreach ($names as $name) {
                    $valName = trim((string) $name);
                    if ($valName !== '') {
                        $value = FilterValue::create([
                            'company_id' => $filter->company_id,
                            'filter_id' => $filter->id,
                            'name' => $valName,
                            'status' => Status::Active->value,
                        ]);
                        $createdValues[] = $value;
                    }
                }
            });

            Log::info('Filter values added successfully', ['filter_id' => $filter->id, 'count' => count($createdValues)]);

            return [
                'count' => count($createdValues),
                'filter_id' => $filter->id,
                'values' => $createdValues,
            ];
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to add filter values: ' . $e->getMessage());
            throw ApiException::serverError('Failed to add filter values');
        }
    }
}

