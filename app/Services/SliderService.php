<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SliderService
{
    /**
     * Get all slider with optional pagination
     */
    public function getAllSliders(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Slider::query();
            if (!empty($filters['select'])) {
                $selectArray = is_string($filters['select']) ? explode(',', $filters['select']) : $filters['select'];
                $query->select($selectArray);
            }

            if (!empty($filters['with'])) {
                $query->with($filters['with']);
            }

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
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
                $customFileName = Str::slug($data['title'] ?? 'slider') . '-' . time();
                
                // 1. Upload Main Image
                $imageFile = $data['image'];
                $data['image'] = FileUploadHelper::uploadImage(
                    $imageFile,
                    'sliders/images',
                    'r2',
                    2048,
                    $customFileName
                );

                // 2. Handle Mobile Image
                if (isset($data['mobile_image'])) {
                    // Upload user-provided mobile image (force resize)
                    $mobileCustomFileName = 'mobile-' . $customFileName;
                    $data['mobile_image'] = FileUploadHelper::uploadResizedWebpImage(
                        $data['mobile_image'],
                        'sliders/images',
                        522,
                        220,
                        'r2',
                        $mobileCustomFileName
                    );
                } else {
                    // Generate mobile image (522x220) from main image
                    $mobileCustomFileName = 'mobile-' . $customFileName;
                    $data['mobile_image'] = FileUploadHelper::uploadResizedWebpImage(
                        $imageFile,
                        'sliders/images',
                        522,
                        220,
                        'r2',
                        $mobileCustomFileName
                    );
                }
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
                $customFileName = Str::slug($data['title'] ?? $slider->title ?? 'slider') . '-' . time();
                
                $imageFile = $data['image'];
                $data['image'] = FileUploadHelper::replace(
                    $imageFile,
                    $slider->image,
                    'sliders/images',
                    'r2',
                    $customFileName
                );

                // Handle Mobile Image
                if (isset($data['mobile_image'])) {
                    $mobileCustomFileName = 'mobile-' . $customFileName;
                    $data['mobile_image'] = FileUploadHelper::replaceResizedWebpImage(
                        $data['mobile_image'],
                        $slider->mobile_image,
                        'sliders/images',
                        522,
                        220,
                        'r2',
                        $mobileCustomFileName
                    );
                } else {
                    // Generate mobile image from new main image
                    $mobileCustomFileName = 'mobile-' . $customFileName;
                    
                    if ($slider->mobile_image) {
                        FileUploadHelper::delete($slider->mobile_image);
                    }

                    $data['mobile_image'] = FileUploadHelper::uploadResizedWebpImage(
                        $imageFile,
                        'sliders/images',
                        522,
                        220,
                        'r2',
                        $mobileCustomFileName
                    );
                }
            } elseif (isset($data['mobile_image'])) {
                // If only mobile image is updated (force resize)
                $customFileName = Str::slug($data['title'] ?? $slider->title ?? 'slider') . '-mobile-' . time();
                $data['mobile_image'] = FileUploadHelper::replaceResizedWebpImage(
                    $data['mobile_image'],
                    $slider->mobile_image,
                    'sliders/images',
                    522,
                    220,
                    'r2',
                    $customFileName
                );
            } elseif (empty($slider->mobile_image) && !empty($slider->image)) {
                // If NO image/mobile_image provided, but mobile_image is empty, generate from existing
                $customFileName = Str::slug($data['title'] ?? $slider->title ?? 'slider') . '-mobile-' . time();
                $data['mobile_image'] = FileUploadHelper::generateResizedFromExisting(
                    $slider->image,
                    'sliders/images',
                    522,
                    220,
                    'r2',
                    $customFileName
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
        DB::beginTransaction();
        try {
            $slider = $this->getSliderById($id);
            $slider->delete();
            LogHelper::deleted('slider', $slider->id, $slider->company_id);

            Log::info('Slider deleted successfully', ['slider_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Slider deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete slider');
        }
    }

    /**
     * Restore soft deleted slider
     */
    public function restoreSlider(int $id): Slider
    {
        DB::beginTransaction();
        try {
            $slider = Slider::withTrashed()->find($id);

            if (!$slider) {
                throw ApiException::notFound('Slider');
            }

            $slider->restore();
            LogHelper::restored('slider', $slider->id, $slider->company_id);

            Log::info('Slider restored successfully', ['slider_id' => $id]);

            DB::commit();
            return $slider;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
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
            if ($slider->mobile_image) {
                FileUploadHelper::delete($slider->mobile_image);
            }

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
        DB::beginTransaction();
        try {
            $slider = $this->getSliderById($id);
            $slider->update(['status' => !$slider->status]);
            LogHelper::statusChanged('slider', $slider->id, $slider->company_id);
            Log::info('Slider status toggled', ['slider_id' => $id]);

            DB::commit();
            return $slider;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Slider status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle slider status');
        }
    }
}
