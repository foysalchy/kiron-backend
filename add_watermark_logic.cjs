const fs = require('fs');
let code = fs.readFileSync('c:/laragon/www/kiron-backend/app/Helpers/FileUploadHelper.php', 'utf8');

const watermarkMethod = `
    private static function applyWatermarkIfNeeded($image, ?int $companyId, string $disk)
    {
        if (!$companyId) return $image;

        $setting = \\App\\Models\\SiteSetting::where('company_id', $companyId)->first();
        if (!$setting || !$setting->watermark_status) return $image;

        $watermarkPath = $setting->watermark_logo ?: $setting->logo;
        if (!$watermarkPath || !\\Illuminate\\Support\\Facades\\Storage::disk($disk)->exists($watermarkPath)) {
            return $image;
        }

        try {
            $manager = new \\Intervention\\Image\\ImageManager(new \\Intervention\\Image\\Drivers\\Gd\\Driver());
            $watermarkContent = \\Illuminate\\Support\\Facades\\Storage::disk($disk)->get($watermarkPath);
            $watermark = $manager->decode($watermarkContent);

            $mainWidth = $image->width();
            $watermarkWidth = intval($mainWidth * 0.20);
            if ($watermarkWidth < 50) $watermarkWidth = 50;

            $watermark->scaleDown(width: $watermarkWidth);
            $image->place($watermark, 'bottom-right', 10, 10);
            
            return $image;
        } catch (\\Exception $e) {
            \\Illuminate\\Support\\Facades\\Log::error('Failed to apply watermark', ['error' => $e->getMessage()]);
            return $image;
        }
    }
`;

if (!code.includes('applyWatermarkIfNeeded')) {
  code = code.replace(
    'class FileUploadHelper\r\n{',
    'class FileUploadHelper\r\n{\r\n' + watermarkMethod
  );
  code = code.replace(
    'class FileUploadHelper\n{',
    'class FileUploadHelper\n{\n' + watermarkMethod
  );
}

code = code.replace(
  /public static function upload\([\s\S]*?\): string \{/,
  `public static function upload(
    UploadedFile $file,
    string $folder = 'uploads',
    string $disk = 'r2',
    bool $preserveName = false,
    ?string $customFileName = null,
    bool $applyWatermark = false
): string {`
);

code = code.replace(
  /public static function uploadImage\([\s\S]*?\): string \{/,
  `public static function uploadImage(
        UploadedFile $file,
        string $folder = 'images',
        string $disk = 'r2',
        int $maxSize = 2048,
        ?string $customFileName = null,
        bool $applyWatermark = false
    ): string {`
);

code = code.replace(
  /return self::upload\(\$file, \$folder, \$disk, false, \$customFileName\);/,
  `return self::upload($file, $folder, $disk, false, $customFileName, $applyWatermark);`
);

code = code.replace(
  /public static function uploadResizedWebpImage\([\s\S]*?\): string \{/,
  `public static function uploadResizedWebpImage(
        \\Illuminate\\Http\\UploadedFile $file,
        string $folder,
        int $width,
        int $height,
        string $disk = 'r2',
        ?string $customFileName = null,
        bool $applyWatermark = false
    ): string {`
);

// Add watermark call to upload method
code = code.replace(
  /\$image = \$manager->decodePath\(\$file->getRealPath\(\)\);\r?\n\s*\$encoded = \$image->encode\(new \\Intervention\\Image\\Encoders\\WebpEncoder\(85\)\);/,
  `$image = $manager->decodePath($file->getRealPath());
                if ($applyWatermark) {
                    $image = self::applyWatermarkIfNeeded($image, $companyId, $disk);
                }
                $encoded = $image->encode(new \\Intervention\\Image\\Encoders\\WebpEncoder(85));`
);

// Add watermark call to uploadResizedWebpImage method
code = code.replace(
  /\$image->cover\(\$width, \$height\);\r?\n\s*\$encoded = \$image->encode\(new \\Intervention\\Image\\Encoders\\WebpEncoder\(85\)\);/,
  `$image->cover($width, $height);
                if ($applyWatermark) {
                    $image = self::applyWatermarkIfNeeded($image, $companyId, $disk);
                }
                $encoded = $image->encode(new \\Intervention\\Image\\Encoders\\WebpEncoder(85));`
);

code = code.replace(
  /return self::upload\(\$file, \$folder, \$disk, false, \$customFileName\);\s*\/\/ from fallback in uploadResizedWebpImage/,
  `return self::upload($file, $folder, $disk, false, $customFileName, $applyWatermark);`
);

fs.writeFileSync('c:/laragon/www/kiron-backend/app/Helpers/FileUploadHelper.php', code);
console.log('Done modifying FileUploadHelper.');
