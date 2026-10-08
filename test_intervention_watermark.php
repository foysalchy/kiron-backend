<?php

require __DIR__.'/vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

$manager = new ImageManager(new Driver());
$image = $manager->create(500, 500)->fill('ffffff');
$watermark = $manager->create(100, 100)->fill('ff0000');

try {
    $image->place($watermark, 'center', 10, 10, 20); // Test opacity 20
    echo "place() with opacity worked\n";
} catch (\Throwable $e) {
    echo "place() failed: " . $e->getMessage() . "\n";
    try {
        $watermark->opacity(20);
        $image->place($watermark, 'center', 0, 0);
        echo "opacity() then place() worked\n";
    } catch (\Throwable $e2) {
        echo "opacity() failed: " . $e2->getMessage() . "\n";
    }
}
