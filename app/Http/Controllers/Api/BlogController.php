<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Services\BlogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\FileUploadHelper;
use Illuminate\Support\Facades\Storage;
class BlogController extends Controller
{
    public function __construct(
        protected BlogService $blogService
    ) {}
    /**
     * Display a listing of the blogs.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status'     => $request->query('status'),
            'search'     => $request->query('search'),
            'sort_by'    => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page'   => $request->query('per_page', 15),
        ];

        $data = $this->blogService->getAllBlogs($filters, true);

        return ResponseHelper::success($data, 'Blogs retrieved successfully');
    }
    /**
     * Store a newly created blog in storage.
     */
    public function store(StoreBlogRequest $request): JsonResponse
    {
        $data = $this->blogService->createBlog($request->validated());

        return ResponseHelper::success($data, 'Blog created successfully', 201);
    }

    /**
     * Display the specified blog.
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->blogService->getBlogById($id);

        return ResponseHelper::success($data, 'Blog retrieved successfully');
    }

    /**
     * Update the specified blog in storage.
     */
    public function update(UpdateBlogRequest $request, int $id): JsonResponse
    {
        $data = $this->blogService->updateBlog($id, $request->validated());

        return ResponseHelper::success($data, 'Blog updated successfully');
    }

    /**
     * Remove the specified blog from storage (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->blogService->deleteBlog($id);

        return ResponseHelper::success(null, 'Blog deleted successfully');
    }

    /**
     * Restore the specified soft-deleted blog.
     */
    public function restore(int $id): JsonResponse
    {
        $data = $this->blogService->restoreBlog($id);

        return ResponseHelper::success($data, 'Blog restored successfully');
    }

    /**
     * Permanently delete the specified blog.
     */
    public function forceDestroy(int $id): JsonResponse
    {
        $this->blogService->forceDeleteBlog($id);

        return ResponseHelper::success(null, 'Blog permanently deleted');
    }

    /**
     * Toggle the blog status (Active/Inactive).
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $data = $this->blogService->toggleStatus($id);

        return ResponseHelper::success($data, 'Blog status updated successfully');
    }
public function uploadImage(Request $request)
{
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    $path = FileUploadHelper::upload(
        file: $request->file('image'),
        folder: 'blogs/content',
        disk: 'public'
    );

    $url = asset('storage/' . $path);

    return response()->json([
        'url' => $url,
    ]);
}
}
