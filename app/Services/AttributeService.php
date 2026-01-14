<?php

namespace App\Services;


use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class AttributeService
{
    /**
     * Get all attributes with optional pagination
     */
    public function getAllAttributes(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query =AttributeValue::with(['company', 'attributeGroup']);

            if (isset($filters['company_id'])) {
                $query->where('company_id', $filters['company_id']);
            }

            if (isset($filters['attribute_group_id'])) {
                $query->where('attribute_group_id', $filters['attribute_group_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate 
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Exception $e) {
            Log::error('Error fetching attributes: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch attributes');
        }
    }
    public function getAttributeValueByCompany(array $filters = [], int $companyId, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query =AttributeValue::with([ 'attributeGroup'])->where('company_id',$companyId);

            if (isset($filters['company_id'])) {
                $query->where('company_id', $filters['company_id']);
            }

            if (isset($filters['attribute_group_id'])) {
                $query->where('attribute_group_id', $filters['attribute_group_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate 
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Exception $e) {
            Log::error('Error fetching attributes: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch attributes');
        }
    }

    /**
     * Get attribute by ID
     */
    public function getAttributeById(int $id): AttributeValue
    {
        $attribute =AttributeValue::with(['company', 'attributeGroup'])->find($id);

        if (!$attribute) {
            throw ApiException::notFound('Attribute');
        }

        return $attribute;
    }

    /**
     * Create a new attribute
     */
    public function createAttribute(array $data): AttributeValue
    {
        try {
            $attribute =AttributeValue::create($data);
            LogHelper::created('attribute_value', $attribute->id, $attribute->company_id);
            Log::info('Attribute created successfully', ['attribute_id' => $attribute->id]);

            return $attribute->load(['company', 'attributeGroup']);

        } catch (\Exception $e) {
            Log::error('Attribute creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create attribute');
        }
    }

    /**
     * Update attribute
     */
    public function updateAttribute(int $id, array $data): AttributeValue
    {
        try {
            $attribute = $this->getAttributeById($id);
            $attribute->update($data);
            LogHelper::updated('attribute_value', $attribute->id, $attribute->company_id);
            Log::info('Attribute updated successfully', ['attribute_id' => $attribute->id]);

            return $attribute->fresh(['company', 'attributeGroup']);

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Attribute update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update attribute');
        }
    }

    /**
     * Delete attribute (soft delete)
     */
    public function deleteAttribute(int $id): bool
    {
        try {
            $attribute = $this->getAttributeById($id);
            $attribute->delete();
            LogHelper::deleted('attribute_value', $attribute->id, $attribute->company_id);

            Log::info('Attribute deleted successfully', ['attribute_id' => $id]);

            return true;

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Attribute deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete attribute');
        }
    }

    /**
     * Restore soft deleted attribute
     */
    public function restoreAttribute(int $id): AttributeValue
    {
        try {
            $attribute =AttributeValue::withTrashed()->find($id);

            if (!$attribute) {
                throw ApiException::notFound('Attribute');
            }

            $attribute->restore();
            LogHelper::restored('attribute_value', $attribute->id, $attribute->company_id);

            Log::info('Attribute restored successfully', ['attribute_id' => $id]);

            return $attribute->load(['company', 'attributeGroup']);

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Attribute restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore attribute');
        }
    }

    /**
     * Permanently delete attribute
     */
    public function forceDeleteAttribute(int $id): bool
    {
        try {
            $attribute =AttributeValue::withTrashed()->find($id);

            if (!$attribute) {
                throw ApiException::notFound('Attribute');
            }

            $attribute->forceDelete();
            LogHelper::forceDeleted('attribute_value', $attribute->id, $attribute->company_id);

            Log::info('Attribute permanently deleted', ['attribute_id' => $id]);

            return true;

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Attribute permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete attribute');
        }
    }

    /**
     * Toggle attribute status
     */
    public function toggleStatus(int $id): AttributeValue
    {
        try {
            $attribute = $this->getAttributeById($id);
            $attribute->update(['status' => !$attribute->status]);
            LogHelper::statusChanged('attribute_value', $attribute->id, $attribute->company_id);

            Log::info('Attribute status toggled', ['attribute_id' => $id]);

            return $attribute->load(['company', 'attributeGroup']);

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Attribute status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle attribute status');
        }
    }

}