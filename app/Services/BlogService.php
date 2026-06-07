<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Blog;
use App\Exceptions\ApiException;
use App\Helpers\{FileUploadHelper, LogHelper};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class BlogService
{
    /**
     * Get all blogs with optional pagination
     */
    public function getAllBlogs(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Blog::query();

            //filter
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }
            if (isset($filters['search']) && $filters['search'] !== '') {
                $query->where(function ($q) use ($filters) {
                    $q->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('short', 'like', "%{$filters['search']}%");
                });
            }
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching blogs: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch blogs');
        }
    }
    /**
     * Get blog by ID
     */
    public function getBlogById(int $id): Blog
    {
        $blog = Blog::find($id);
        if (!$blog) {
            throw ApiException::notFound('Blog');
        }
        return $blog;
    }
    /**
     * Create a new blog
     */
    public function createBlog(array $data): Blog
    {
        DB::beginTransaction();

        try {
            $data['user_id'] = auth()->id();
            // Handle multiple image uploads for JSON column
            if (isset($data['images']) && is_array($data['images'])) {
                $uploadedImages = [];
                foreach ($data['images'] as $image) {
                    $uploadedImages[] = FileUploadHelper::uploadImage(
                        $image,
                        'blogs/images',

                    );
                }
                $data['images'] = $uploadedImages;
            }

            $blog = Blog::create($data);

            LogHelper::created('blog', $blog->id, $blog->company_id, $blog->title);
            DB::commit();

            Log::info('Blog created successfully', ['blog_id' => $blog->id]);

            return $blog;
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded images if DB fails
            if (isset($uploadedImages)) {
                foreach ($uploadedImages as $path) {
                    FileUploadHelper::delete($path);
                }
            }

            Log::error('Blog creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create blog');
        }
    }
    /**
     * Update blog
     */
    public function updateBlog(int $id, array $data): Blog
    {
        DB::beginTransaction();

        try {
            $blog = $this->getBlogById($id);

            $currentImages = $blog->images ?? [];

            $existingImagesKept = $data['existing_images'] ?? [];

            $imagesToDelete = array_diff($currentImages, $existingImagesKept);

            foreach ($imagesToDelete as $imagePath) {
                FileUploadHelper::delete($imagePath);
            }

            $finalImages = $existingImagesKept;


            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    $finalImages[] = FileUploadHelper::uploadImage(
                        $image,
                        'blogs/images',

                    );
                }
            }
            $data['images'] = $finalImages;

            $blog->update($data);

            LogHelper::updated('blog', $blog->id, $blog->company_id, $blog->title);
            DB::commit();

            Log::info('Blog updated successfully', ['blog_id' => $blog->id]);

            return $blog->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Blog update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update blog');
        }
    }

    /**
     * Delete blog (soft delete)
     */
    public function deleteBlog(int $id): bool
    {
        DB::beginTransaction();
        try {
            $blog = $this->getBlogById($id);
            $blog->delete();

            LogHelper::deleted('blog', $id, $blog->company_id, $blog->title);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Blog deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete blog');
        }
    }
    /**
     * Restore soft deleted blog
     */
    public function restoreBlog(int $id): Blog
    {
        DB::beginTransaction();
        try {
            $blog = Blog::withTrashed()->find($id);

            if (!$blog) {
                throw ApiException::notFound('Blog');
            }

            $blog->restore();
            LogHelper::restored('blog', $blog->id, $blog->company_id, $blog->title);

            Log::info('Blog restored successfully', ['blog_id' => $id]);
            DB::commit();
            return $blog;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Blog restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore blog');
        }
    }

    /**
     * Permanently delete blog
     */
    public function forceDeleteBlog(int $id): bool
    {
        DB::beginTransaction();

        try {
            $blog = Blog::withTrashed()->find($id);

            if (!$blog) {
                throw ApiException::notFound('Blog');
            }

            // Delete images from storage
            if (!empty($blog->images)) {
                foreach ($blog->images as $path) {
                    FileUploadHelper::delete($path);
                }
            }

            $blog->forceDelete();

            DB::commit();

            LogHelper::forceDeleted('blog', $id, $blog->company_id, $blog->title);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Blog permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete blog');
        }
    }

    /**
     * Toggle blog status (Active/Inactive)
     */
    public function toggleStatus(int $id): Blog
    {
        DB::beginTransaction();
        try {
            $blog = $this->getBlogById($id);

            // current status as enum
            $currentStatus = Status::from($blog->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $blog->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('blog', $blog->id, $blog->company_id, $blog->title . ' new status ' . $newStatus->label());

            DB::commit();
            Log::info('Blog status toggled successfully', ['blog_id' => $id, 'new_status' => $newStatus->label()]);

            return $blog;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Blog status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle blog status');
        }
    }
}
