<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\LandingPage;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LandingPageService
{
    /**
     * Get all landing pages with filters
     */
    public function getAllLandingPages(array $filters = [], bool $paginate = true)
    {
        try {
            $query = LandingPage::query();

            // Apply filters
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (isset($filters['template_id'])) {
                $query->where('template_id', $filters['template_id']);
            }

            if (isset($filters['product_id'])) {
                $query->where('product_id', $filters['product_id']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('title', 'like', "%{$filters['search']}%")
                        ->orWhere('slug', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching landing pages: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch landing pages');
        }
    }

    /**
     * Get landing page by ID
     */
    public function getLandingPageById(int $id): LandingPage
    {
        $landingPage = LandingPage::with(['product'])->find($id);

        if (!$landingPage) {
            throw ApiException::notFound('Landing Page');
        }

        $landingPage->append(['products', 'product_ids']);

        return $landingPage;
    }
    public function getLandingProductById(int $id, ?string $search = null)
    {
        $query = Product::select('id', 'title', 'thumbnail_95')
            ->where('status', Status::Active->value);

        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        return $query->paginate(15);
    }

    /**
     * Get landing page by slug
     */
    public function getLandingPageBySlug(string $slug): LandingPage
    {
        $landingPage = LandingPage::where('slug', $slug)
            ->first();

        if (!$landingPage) {
            throw ApiException::notFound('Landing Page');
        }

        return $landingPage;
    }

    /**
     * Create a new landing page
     */
    public function createLandingPage(array $data): LandingPage
    {
        DB::beginTransaction();
        try {
            if (isset($data['thumbnail']) && !is_string($data['thumbnail'])) {
                $customFileName = Str::slug($data['title'] ?? $data['name'] ?? 'landing-page') . '_thumb_' . time();
                $data['thumbnail'] = FileUploadHelper::uploadImage(
                    $data['thumbnail'],
                    'landing-pages/thumbnails',
                    'r2',
                    2048,
                    $customFileName
                );
            } elseif (empty($data['thumbnail']) && isset($data['product_id'])) {
                $product = \App\Models\Product::find($data['product_id']);
                if ($product && $product->thumbnail) {
                    $data['thumbnail'] = $product->thumbnail;
                } else {
                    unset($data['thumbnail']);
                }
            }

            if (isset($data['video'])) {
                $customFileName = Str::slug($data['title'] ?? $data['name'] ?? 'landing-page') . '_video_' . time();
                $data['video'] = FileUploadHelper::upload(
                    $data['video'],
                    'landing-pages/videos',
                    'r2',
                    false,
                    $customFileName
                );
            }

            if (isset($data['meta_image']) && !is_string($data['meta_image'])) {
                $customFileName = Str::slug($data['title'] ?? $data['name'] ?? 'landing-page') . '_meta_' . time();
                $data['meta_image'] = FileUploadHelper::uploadImage(
                    $data['meta_image'],
                    'landing-pages/meta-images',
                    'r2',
                    2048,
                    $customFileName
                );
            }


            $request = request();
            if ($request->hasFile('img_paths')) {
                $newPaths = [];
                foreach ($request->file('img_paths') as $index => $file) {
                    $customFileName = Str::slug($data['title'] ?? $data['name'] ?? 'landing-page') . '_img_' . ($index + 1) . '-' . time();
                    $newPaths[] = FileUploadHelper::uploadImage(
                        $file,
                        'landing-pages/images',
                        'r2',
                        2048,
                        $customFileName
                    );
                }
                $data['img_paths'] = $newPaths;
            }
            // Multi-product support via product_ids
            if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
                $cleanProductIds = array_values(array_filter(array_map('intval', $data['product_ids'])));
                if (!empty($cleanProductIds)) {
                    if (empty($data['product_id'])) {
                        $data['product_id'] = $cleanProductIds[0];
                    }
                    if (!isset($data['extras'])) {
                        $data['extras'] = [];
                    } elseif (is_string($data['extras'])) {
                        $data['extras'] = json_decode($data['extras'], true) ?? [];
                    }
                    $data['extras']['product_ids'] = $cleanProductIds;
                }
            }
            unset($data['product_ids']);

            if (isset($data['extras']) && is_string($data['extras'])) {
                $data['extras'] = json_decode($data['extras'], true);
            }

            if (isset($data['extras'])) {
                $data['extras'] = $this->handleExtrasImages(
                    $data['extras'],
                    [],
                    $request,
                    Str::slug($data['title'] ?? $data['name'] ?? 'landing-page')
                );
            }

            $landingPage = LandingPage::create($data);

            LogHelper::created('landing_page', $landingPage->id, $landingPage->company_id, $landingPage->name);

            DB::commit();
            return $landingPage;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['thumbnail'])) FileUploadHelper::delete($data['thumbnail']);
            if (isset($data['video'])) FileUploadHelper::delete($data['video']);
            Log::error('Landing page creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create landing page');
        }
    }

    // Card extras image upload helper
    private function handleExtrasImages(array $extras, array $existingExtras = [], $request, string $baseSlug = 'landing-page'): array
    {
        foreach ($extras as $fieldName => &$fieldValue) {
            if (is_array($fieldValue) && !empty($fieldValue) && is_array($fieldValue[0])) {
                foreach ($fieldValue as $i => &$card) {
                    $fileKey = "extras_{$fieldName}_image_{$i}";

                    if ($request->hasFile($fileKey)) {
                        $oldImage = $existingExtras[$fieldName][$i]['image'] ?? null;

                        $customFileName = $baseSlug . '_extra_' . $fieldName . '_' . ($i + 1) . '-' . time();

                        $card['image'] = $oldImage
                            ? FileUploadHelper::replace(
                                $request->file($fileKey),
                                $oldImage,
                                'landing-pages/extras',
                                'r2',
                                $customFileName
                            )
                            : FileUploadHelper::uploadImage(
                                $request->file($fileKey),
                                'landing-pages/extras',
                                'r2',
                                2048,
                                $customFileName
                            );
                    } elseif (isset($card['image']) && str_starts_with((string)$card['image'], '__file__')) {
                        $card['image'] = null;
                    } elseif (!array_key_exists('image', $card) || $card['image'] === null) {
                        // image field আসেনি — existing রেখে দাও
                        $card['image'] = $existingExtras[$fieldName][$i]['image'] ?? null;
                    }
                }
            }
        }

        return $extras;
    }
    /**
     * Update landing page
     */
    public function updateLandingPage(int $id, array $data): LandingPage
    {
        DB::beginTransaction();
        try {
            $landingPage = $this->getLandingPageById($id);

            if (isset($data['thumbnail']) && !is_string($data['thumbnail'])) {
                $customFileName = Str::slug($data['title'] ?? $data['name'] ?? $landingPage->title ?? 'landing-page') . '_thumb_' . time();
                $data['thumbnail'] = FileUploadHelper::replace(
                    $data['thumbnail'],
                    $landingPage->thumbnail,
                    'landing-pages/thumbnails',
                    'r2',
                    $customFileName
                );
            } elseif (empty($landingPage->thumbnail) && (empty($data['thumbnail']) || is_string($data['thumbnail'])) && isset($data['product_id'])) {
                $product = \App\Models\Product::find($data['product_id']);
                if ($product && $product->thumbnail) {
                    $data['thumbnail'] = $product->thumbnail;
                } else {
                    unset($data['thumbnail']);
                }
            }

            if (!empty($data['remove_video'])) {
                if ($landingPage->video) {
                    FileUploadHelper::delete($landingPage->video);
                }
                $data['video'] = null;
            } elseif (isset($data['video']) && !is_string($data['video'])) {
                $customFileName = Str::slug($data['title'] ?? $data['name'] ?? $landingPage->title ?? 'landing-page') . '_video_' . time();
                $data['video'] = FileUploadHelper::replace(
                    $data['video'],
                    $landingPage->video,
                    'landing-pages/videos',
                    'r2',
                    $customFileName
                );
            }

            if (!empty($data['remove_meta_image'])) {
                if ($landingPage->meta_image) {
                    FileUploadHelper::delete($landingPage->meta_image);
                }
                $data['meta_image'] = null;
            } elseif (isset($data['meta_image']) && !is_string($data['meta_image'])) {
                $customFileName = Str::slug($data['title'] ?? $data['name'] ?? $landingPage->title ?? 'landing-page') . '_meta_' . time();
                $data['meta_image'] = FileUploadHelper::replace(
                    $data['meta_image'],
                    $landingPage->meta_image,
                    'landing-pages/meta-images',
                    'r2',
                    $customFileName
                );
            }

            // Multiple images
            $request = request();
            if ($request->hasFile('img_paths')) {
                $existingPaths = $data['existing_img_paths'] ?? [];

                // Remove হয়ে যাওয়া পুরনো images delete করো
                $oldPaths = $landingPage->img_paths ?? [];
                foreach (array_diff($oldPaths, $existingPaths) as $removed) {
                    FileUploadHelper::delete($removed);
                }

                // নতুন upload
                $newPaths = [];
                foreach ($request->file('img_paths') as $index => $file) {
                    $customFileName = Str::slug($data['title'] ?? $data['name'] ?? $landingPage->title ?? 'landing-page') . '_img_' . ($index + 1) . '-' . time();
                    $newPaths[] = FileUploadHelper::uploadImage(
                        $file,
                        'landing-pages/images',
                        'r2',
                        2048,
                        $customFileName
                    );
                }

                $data['img_paths'] = array_merge($existingPaths, $newPaths);
            } else {
                // কোনো নতুন file নেই — existing রেখে দাও
                $data['img_paths'] = $data['existing_img_paths'] ?? $landingPage->img_paths ?? [];
            }
            unset($data['existing_img_paths']);

            // Multi-product support via product_ids
            if (array_key_exists('product_ids', $data)) {
                $existingExtras = is_string($landingPage->extras) ? json_decode($landingPage->extras, true) : ($landingPage->extras ?? []);
                if (is_array($data['product_ids']) && !empty($data['product_ids'])) {
                    $cleanProductIds = array_values(array_filter(array_map('intval', $data['product_ids'])));
                    if (empty($data['product_id']) && !empty($cleanProductIds)) {
                        $data['product_id'] = $cleanProductIds[0];
                    }
                    if (!isset($data['extras'])) {
                        $data['extras'] = $existingExtras;
                    } elseif (is_string($data['extras'])) {
                        $data['extras'] = json_decode($data['extras'], true) ?? [];
                    }
                    $data['extras']['product_ids'] = $cleanProductIds;
                } else {
                    if (isset($data['extras']) && is_array($data['extras'])) {
                        unset($data['extras']['product_ids']);
                    }
                }
            }
            unset($data['product_ids']);

            if (isset($data['extras']) && is_string($data['extras'])) {
                $data['extras'] = json_decode($data['extras'], true);
            }

            if (isset($data['extras'])) {
                $existingExtras = $landingPage->extras ?? [];
                $data['extras'] = $this->handleExtrasImages(
                    $data['extras'],
                    $existingExtras,
                    $request,
                    Str::slug($data['title'] ?? $data['name'] ?? $landingPage->title ?? 'landing-page')
                );
            }

            $landingPage->update($data);

            LogHelper::updated('landing_page', $landingPage->id, $landingPage->company_id, $landingPage->name);

            DB::commit();
            return $landingPage;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['thumbnail'])) FileUploadHelper::delete($data['thumbnail']);
            if (isset($data['video'])) FileUploadHelper::delete($data['video']);
            Log::error('Landing page update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update landing page');
        }
    }

    /**
     * Delete landing page (soft delete)
     */
    public function deleteLandingPage(int $id): bool
    {
        try {
            $landingPage = $this->getLandingPageById($id);

            $landingPage->delete();

            LogHelper::deleted('landing_page', $landingPage->id, $landingPage->company_id, $landingPage->name);
            Log::info('Landing page deleted successfully', ['landing_page_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Landing page deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete landing page');
        }
    }

    /**
     * Restore soft deleted landing page
     */
    public function restoreLandingPage(int $id): LandingPage
    {
        try {
            $landingPage = LandingPage::withTrashed()->find($id);

            if (!$landingPage) {
                throw ApiException::notFound('Landing Page');
            }

            $landingPage->restore();

            LogHelper::restored('landing_page', $landingPage->id, $landingPage->company_id, $landingPage->name);
            Log::info('Landing page restored successfully', ['landing_page_id' => $id]);

            return $landingPage;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Landing page restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore landing page');
        }
    }

    /**
     * Permanently delete landing page
     */
    public function forceDeleteLandingPage(int $id): bool
    {
        DB::beginTransaction();
        try {
            $landingPage = LandingPage::withTrashed()->find($id);

            if (!$landingPage) {
                throw ApiException::notFound('Landing Page');
            }

            // Clean up all R2 files
            $this->deleteLandingPageFiles($landingPage);

            $landingPage->forceDelete();

            LogHelper::forceDeleted('landing_page', $landingPage->id, $landingPage->company_id, $landingPage->name);

            DB::commit();
            Log::info('Landing page permanently deleted', ['landing_page_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Landing page permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete landing page');
        }
    }

    /**
     * Delete all files belonging to a landing page from R2 storage
     */
    private function deleteLandingPageFiles(LandingPage $landingPage): void
    {
        // 1. Delete thumbnail (only if not inherited from product)
        if ($landingPage->thumbnail) {
            $isProductThumb = false;
            if ($landingPage->product_id) {
                $product = $landingPage->product ?? \App\Models\Product::find($landingPage->product_id);
                if ($product && $product->thumbnail === $landingPage->thumbnail) {
                    $isProductThumb = true;
                }
            }

            if (!$isProductThumb) {
                FileUploadHelper::delete($landingPage->thumbnail);
            }
        }

        // 2. Delete video
        if ($landingPage->video) {
            FileUploadHelper::delete($landingPage->video);
        }

        // 3. Delete multiple review images (img_paths)
        if (!empty($landingPage->img_paths) && is_array($landingPage->img_paths)) {
            foreach ($landingPage->img_paths as $imgPath) {
                if ($imgPath && is_string($imgPath)) {
                    FileUploadHelper::delete($imgPath);
                }
            }
        }

        // 4. Delete all card images in extras
        if (!empty($landingPage->extras) && is_array($landingPage->extras)) {
            foreach ($landingPage->extras as $fieldValue) {
                if (is_array($fieldValue)) {
                    foreach ($fieldValue as $card) {
                        if (is_array($card) && !empty($card['image']) && is_string($card['image'])) {
                            FileUploadHelper::delete($card['image']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Toggle landing page status
     */
    public function toggleStatus(int $id): LandingPage
    {
        try {
            $landingPage = $this->getLandingPageById($id);

            // Current status as enum
            $currentStatus = Status::from($landingPage->status);

            // Toggle logic
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            // Update using enum value
            $landingPage->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged(
                'landing_page',
                $landingPage->id,
                $landingPage->company_id,
                $landingPage->name . ' new status ' . $newStatus->label()
            );

            Log::info('Landing page status toggled', [
                'landing_page_id' => $id,
                'new_status' => $newStatus->label()
            ]);

            return $landingPage;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Landing page status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle landing page status');
        }
    }
}
