<?php

namespace App\Helpers;

use App\Exceptions\ApiException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadHelper
{

    public static function upload(
        UploadedFile $file,
        string $folder = 'uploads',
        string $disk = 'r2',
        bool $preserveName = false
    ): string {
        try {
            if ($preserveName) {
                $fileName = $file->getClientOriginalName();
                return $file->storeAs($folder, $fileName, $disk);
            }

            return $file->store($folder, $disk);
        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'folder' => $folder,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            throw ApiException::serverError('Failed to upload file');
        }
    }

    /**
     * Upload image with validation
     */
    public static function uploadImage(
        UploadedFile $file,
        string $folder = 'images',
        string $disk = 'r2',
        int $maxSize = 2048
    ): string {
        try {
            // Validate image
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

            if (!in_array($file->getMimeType(), $allowedMimes)) {
                throw ApiException::badRequest('Invalid image format. Allowed: jpeg, png, jpg, webp');
            }

            // Check file size (in KB)
            if ($file->getSize() > ($maxSize * 1024)) {
                throw ApiException::badRequest("Image size cannot exceed {$maxSize}KB");
            }

            return self::upload($file, $folder, $disk);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Image upload failed', [
                'folder' => $folder,
                'error' => $e->getMessage()
            ]);
            throw ApiException::serverError('Failed to upload image');
        }
    }


    public static function uploadWithCustomName(
        UploadedFile $file,
        string $folder,
        string $customName,
        string $disk = 'public'
    ): string {
        try {
            $extension = $file->getClientOriginalExtension();
            $fileName = $customName . '.' . $extension;

            return $file->storeAs($folder, $fileName, $disk);
        } catch (\Exception $e) {
            Log::error('File upload with custom name failed', [
                'folder' => $folder,
                'custom_name' => $customName,
                'error' => $e->getMessage()
            ]);
            throw ApiException::serverError('Failed to upload file');
        }
    }



    public static function delete(?string $filePath, string $disk = 'public'): bool
    {
        try {
            if (!$filePath || !Storage::disk($disk)->exists($filePath)) {
                return false;
            }

            return Storage::disk($disk)->delete($filePath);
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'file_path' => $filePath,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function replace(
        UploadedFile $newFile,
        ?string $oldFilePath,
        string $folder,
        string $disk = 'r2'
    ): string {
        try {
            // Upload new file
            $newFilePath = self::upload($newFile, $folder, $disk);

            // Delete old file
            if ($oldFilePath) {
                self::delete($oldFilePath, $disk);
            }

            return $newFilePath;
        } catch (\Exception $e) {
            Log::error('File replacement failed', [
                'old_file' => $oldFilePath,
                'folder' => $folder,
                'error' => $e->getMessage()
            ]);
            throw ApiException::serverError('Failed to replace file');
        }
    }


    public static function getUrl(?string $filePath, string $disk = 'public'): ?string
    {
        if (!$filePath) {
            return null;
        }

        try {
            return Storage::disk($disk)->url($filePath);
        } catch (\Exception $e) {
            Log::error('Failed to get file URL', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }


    public static function exists(?string $filePath, string $disk = 'public'): bool
    {
        if (!$filePath) {
            return false;
        }

        return Storage::disk($disk)->exists($filePath);
    }

    public static function getSize(string $filePath, string $disk = 'public'): ?float
    {
        try {
            if (!self::exists($filePath, $disk)) {
                return null;
            }

            return round(Storage::disk($disk)->size($filePath) / 1024, 2);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Upload multiple files
     */
    public static function uploadMultiple(
        array $files,
        string $folder = 'uploads',
        string $disk = 'public'
    ): array {
        $uploadedPaths = [];

        try {
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $uploadedPaths[] = self::upload($file, $folder, $disk);
                }
            }

            return $uploadedPaths;
        } catch (\Exception $e) {
            // Delete all uploaded files if any upload fails
            foreach ($uploadedPaths as $path) {
                self::delete($path, $disk);
            }

            Log::error('Multiple files upload failed', [
                'folder' => $folder,
                'error' => $e->getMessage()
            ]);
            throw ApiException::serverError('Failed to upload files');
        }
    }


    public static function deleteMultiple(array $filePaths, string $disk = 'public'): bool
    {
        try {
            foreach ($filePaths as $filePath) {
                self::delete($filePath, $disk);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Multiple files deletion failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
