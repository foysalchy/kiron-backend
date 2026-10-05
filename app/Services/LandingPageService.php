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

        return $landingPage;
    }
    public function getLandingProductById(int $id)
    {
        $product = Product::with([
            'brand',
            'galleries',
            'variations.attributes.attributeValue.attributeGroup',
            'variations.stocks.warehouse',
            'variations.galleries',

        ])->limit(5)->get();

        return $product;
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
            if (isset($data['thumbnail'])) {
                $customFileName = Str::slug($data['title'] ?? $data['name'] ?? 'landing-page') . '_thumb_' . time();
                $data['thumbnail'] = FileUploadHelper::uploadImage(
                    $data['thumbnail'],
                    'landing-pages/thumbnails',
                    'r2',
                    2048,
                    $customFileName
                );
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
            unset($data['existing_img_paths']); // create-এ দরকার নেই

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
            if (is_array($fieldValue) && !empty($fieldValue) && isset($fieldValue[0]['title'])) {
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
            }

            if (isset($data['video'])) {
                $customFileName = Str::slug($data['title'] ?? $data['name'] ?? $landingPage->title ?? 'landing-page') . '_video_' . time();
                $data['video'] = FileUploadHelper::replace(
                    $data['video'],
                    $landingPage->video,
                    'landing-pages/videos',
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

            // Delete files
            FileUploadHelper::delete($landingPage->thumbnail);
            FileUploadHelper::delete($landingPage->video);

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
