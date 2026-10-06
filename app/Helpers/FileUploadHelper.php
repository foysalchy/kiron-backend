<?php

namespace App\Helpers;

use App\Exceptions\ApiException;
use App\Models\DomainSetup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadHelper
{

public static function upload(
    UploadedFile $file,
    string $folder = 'uploads',
    string $disk = 'r2',
    bool $preserveName = false,
    ?string $customFileName = null
): string {
    try {
        $user = auth()->user();

        $companyId = $user?->company_id;

        if ($companyId === null) {
            // Super Admin / user without company
            $companyFolder = 'admin';
        } else {
            $prefix = self::getCompanyPrefix((int) $companyId);
            $companyFolder = "{$prefix}_{$companyId}";
        }

        $fullFolder = "{$companyFolder}/{$folder}";
        
        $options = [
            'disk' => $disk,
        ];
        
        // Add cache control headers for public disks
        if ($disk === 'r2' || $disk === 's3' || $disk === 'public') {
            $options['CacheControl'] = 'public, max-age=31536000, immutable';
        }

        if ($customFileName) {
            $extension = $file->getClientOriginalExtension();
            $fileName = $customFileName . '.' . $extension;
        } elseif ($preserveName) {
            $fileName = $file->getClientOriginalName();
        } else {
            $fileName = $file->hashName();
        }

        $isImage = str_starts_with($file->getMimeType(), 'image/');
        $isSvg = strtolower($file->getClientOriginalExtension()) === 'svg';
        $isWebp = strtolower($file->getClientOriginalExtension()) === 'webp';

        // Convert image to WebP if it's not SVG and not already WebP
        if ($isImage && !$isSvg && class_exists('\Intervention\Image\ImageManager')) {
            try {
                // Use Intervention Image v4 syntax
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $image = $manager->decodePath($file->getRealPath());
                $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(85));

                $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
                $fileName = $fileNameWithoutExt . '.webp';
                $fullPath = "{$fullFolder}/{$fileName}";

                Storage::disk($disk)->put($fullPath, (string) $encoded, $options);
                return $fullPath;
            } catch (\Exception $e) {
                Log::error('WebP conversion failed, falling back to original', ['error' => $e->getMessage()]);
                return $file->storeAs($fullFolder, $fileName, $options);
            }
        }

        return $file->storeAs($fullFolder, $fileName, $options);

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
        int $maxSize = 2048,
        ?string $customFileName = null
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

            return self::upload($file, $folder, $disk, false, $customFileName);
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

    /**
     * Upload and resize image to specific dimensions as WebP
     */
    public static function uploadResizedWebpImage(
        \Illuminate\Http\UploadedFile $file,
        string $folder,
        int $width,
        int $height,
        string $disk = 'r2',
        ?string $customFileName = null
    ): string {
        try {
            $user = auth()->user();
            $companyId = $user?->company_id;
            $companyFolder = $companyId === null ? 'admin' : self::getCompanyPrefix((int) $companyId) . "_{$companyId}";
            $fullFolder = "{$companyFolder}/{$folder}";

            $fileName = $customFileName ? $customFileName . '.webp' : Str::random(40) . '.webp';
            $fullPath = "{$fullFolder}/{$fileName}";

            $options = ['disk' => $disk];
            if (in_array($disk, ['r2', 's3', 'public'])) {
                $options['CacheControl'] = 'public, max-age=31536000, immutable';
            }

            if (class_exists('\Intervention\Image\ImageManager')) {
                // v4 syntax
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $image = $manager->decodePath($file->getRealPath());
                
                // Crop to exact dimension or scale down
                $image->cover($width, $height);
                $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(85));
                
                Storage::disk($disk)->put($fullPath, (string) $encoded, $options);
                return $fullPath;
            }

            // Fallback if Intervention is somehow missing
            return self::upload($file, $folder, $disk, false, $customFileName);

        } catch (\Exception $e) {
            Log::error('Resized image upload failed', [
                'folder' => $folder,
                'error' => $e->getMessage()
            ]);
            throw ApiException::serverError('Failed to upload resized image');
        }
    }

    /**
     * Generate and upload resized image from an existing path
     */
    public static function generateResizedFromExisting(
        string $existingPath,
        string $folder,
        int $width,
        int $height,
        string $disk = 'r2',
        ?string $customFileName = null
    ): string {
        try {
            $user = auth()->user();
            $companyId = $user?->company_id;
            $companyFolder = $companyId === null ? 'admin' : self::getCompanyPrefix((int) $companyId) . "_{$companyId}";
            $fullFolder = "{$companyFolder}/{$folder}";

            $fileName = $customFileName ? $customFileName . '.webp' : Str::random(40) . '.webp';
            $fullPath = "{$fullFolder}/{$fileName}";

            $options = ['disk' => $disk];
            if (in_array($disk, ['r2', 's3', 'public'])) {
                $options['CacheControl'] = 'public, max-age=31536000, immutable';
            }

            if (class_exists('\Intervention\Image\ImageManager')) {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $imageContent = Storage::disk($disk)->get($existingPath);
                
                if (!$imageContent) {
                    throw new \Exception("Existing file not found on disk: " . $existingPath);
                }

                $image = $manager->decode($imageContent);
                $image->cover($width, $height);
                $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(85));
                
                Storage::disk($disk)->put($fullPath, (string) $encoded, $options);
                return $fullPath;
            }

            throw new \Exception("Intervention Image is not installed or available.");
        } catch (\Exception $e) {
            Log::error('Generate resized image failed', [
                'existingPath' => $existingPath,
                'error' => $e->getMessage()
            ]);
            throw ApiException::serverError('Failed to generate resized image: ' . $e->getMessage());
        }
    }

    public static function copyFile(string $sourcePath, string $destinationFolder, string $disk = 'r2'): string
    {
        try {
            $filename = 'copy_' . time() . '_' . uniqid() . '_' . basename($sourcePath);
            $destinationPath = $destinationFolder . '/' . $filename;

            $fileContent = Storage::disk($disk)->get($sourcePath);

            if ($fileContent === null) {
                throw ApiException::badRequest('Source file not found: ' . $sourcePath);
            }

            Storage::disk($disk)->put($destinationPath, $fileContent);

            return $destinationPath;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('File copy failed', [
                'source'      => $sourcePath,
                'destination' => $destinationFolder,
                'error'       => $e->getMessage(),
            ]);
            throw ApiException::serverError('Failed to copy file');
        }
    }
    public static function uploadWithCustomName(
        UploadedFile $file,
        string $folder,
        string $customName,
        string $disk = 'r2'
    ): string {
        try {
            $extension = $file->getClientOriginalExtension();
            $fileName = $customName . '.' . $extension;

            $options = [
                'disk' => $disk,
            ];
            
            if ($disk === 'r2' || $disk === 's3' || $disk === 'public') {
                $options['CacheControl'] = 'public, max-age=31536000, immutable';
            }

            return $file->storeAs($folder, $fileName, $options);
        } catch (\Exception $e) {
            Log::error('File upload with custom name failed', [
                'folder' => $folder,
                'custom_name' => $customName,
                'error' => $e->getMessage()
            ]);
            throw ApiException::serverError('Failed to upload file');
        }
    }



    public static function delete(?string $filePath, string $disk = 'r2'): bool
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
        string $disk = 'r2',
        ?string $customFileName = null
    ): string {
        try {
            // Upload new file
            $newFilePath = self::upload($newFile, $folder, $disk, false, $customFileName);

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

    /**
     * Replace an existing image with a new resized WebP image
     */
    public static function replaceResizedWebpImage(
        \Illuminate\Http\UploadedFile $newFile,
        ?string $oldFilePath,
        string $folder,
        int $width,
        int $height,
        string $disk = 'r2',
        ?string $customFileName = null
    ): string {
        try {
            $newFilePath = self::uploadResizedWebpImage($newFile, $folder, $width, $height, $disk, $customFileName);

            if ($oldFilePath) {
                self::delete($oldFilePath, $disk);
            }

            return $newFilePath;
        } catch (\Exception $e) {
            Log::error('Resized file replacement failed', [
                'old_file' => $oldFilePath,
                'folder' => $folder,
                'error' => $e->getMessage()
            ]);
            throw ApiException::serverError('Failed to replace resized file');
        }
    }


    public static function getUrl(?string $filePath, string $disk = 'r2'): ?string
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


    public static function exists(?string $filePath, string $disk = 'r2'): bool
    {
        if (!$filePath) {
            return false;
        }

        return Storage::disk($disk)->exists($filePath);
    }

    public static function getSize(string $filePath, string $disk = 'r2'): ?float
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
        string $disk = 'r2'
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


    public static function deleteMultiple(array $filePaths, string $disk = 'r2'): bool
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
    protected static function getCompanyPrefix(int $companyId): string
    {
        return Cache::rememberForever("domain_prefix_company_{$companyId}", function () use ($companyId) {
            $domainSetup = DomainSetup::where('company_id', $companyId)->first();
            return $domainSetup->prefix ?? 'default';
        });
    }
}
