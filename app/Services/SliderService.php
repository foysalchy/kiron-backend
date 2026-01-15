<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SliderService
{
    /**
     * Get all slider with optional pagination
     */
    public function getAllSliders(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Slider::query();
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['title']) && $filters['title'] !== '') {
                $query->where('title', 'like', "%{$filters['title']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching sliders: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch sliders');
        }
    }

    /**
     * Get slider by ID
     */
    public function getSliderById(int $id): Slider
    {
        $slider = Slider::find($id);

        if (!$slider) {
            throw ApiException::notFound('Slider');
        }

        return $slider;
    }

    /**
     * Create a new slider
     */
    public function createSlider(array $data): Slider
    {
        DB::beginTransaction();

        try {
            // Handle image upload
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'sliders/images',
                    'public',
                    2048
                );
            }

            $slider = Slider::create($data);
            LogHelper::created('slider', $slider->id, $slider->company_id);

            DB::commit();

            Log::info('Slider created successfully', ['slider_id' => $slider->id]);

            return $slider;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Slider creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create slider');
        }
    }

    /**
     * Update slider
     */
    public function updateSlider(int $id, array $data): Slider
    {
        DB::beginTransaction();

        try {
            $slider = $this->getSliderById($id);

            // Handle image upload
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $slider->image,
                    'sliders/images'
                );
            }

            $slider->update($data);
            LogHelper::updated('slider', $slider->id, $slider->company_id);

            DB::commit();

            Log::info('Slider updated successfully', ['slider_id' => $slider->id]);

            return $slider;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Slider update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update slider');
        }
    }

    /**
     * Delete slider (soft delete)
     */
    public function deleteSlider(int $id): bool
    {
        try {
            $slider = $this->getSliderById($id);
            $slider->delete();
            LogHelper::deleted('slider', $slider->id, $slider->company_id);

            Log::info('Slider deleted successfully', ['slider_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Slider deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete slider');
        }
    }

    /**
     * Restore soft deleted slider
     */
    public function restoreSlider(int $id): Slider
    {
        try {
            $slider = Slider::withTrashed()->find($id);

            if (!$slider) {
                throw ApiException::notFound('Slider');
            }

            $slider->restore();
            LogHelper::restored('slider', $slider->id, $slider->company_id);

            Log::info('Slider restored successfully', ['slider_id' => $id]);

            return $slider;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Slider restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore slider');
        }
    }

    /**
     * Permanently delete slider
     */
    public function forceDeleteSlider(int $id): bool
    {
        DB::beginTransaction();

        try {
            $slider = Slider::withTrashed()->find($id);

            if (!$slider) {
                throw ApiException::notFound('Slider');
            }

            // Delete logo
            FileUploadHelper::delete($slider->image);

            $slider->forceDelete();
            LogHelper::forceDeleted('slider', $slider->id, $slider->company_id);

            DB::commit();

            Log::info('Slider permanently deleted', ['slider_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Slider permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete slider');
        }
    }

    /**
     * Toggle slider status
     */
    public function toggleStatus(int $id): Slider
    {
        try {
            $slider = $this->getSliderById($id);
            $slider->update(['status' => !$slider->status]);
            LogHelper::statusChanged('slider', $slider->id, $slider->company_id);
            Log::info('Slider status toggled', ['slider_id' => $id]);

            return $slider;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Slider status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle slider status');
        }
    }
}
