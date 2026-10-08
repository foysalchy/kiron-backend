<?php
require 'vendor/autoload.php';
$manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
$image = $manager->create(100, 100);
$watermark = $manager->create(20, 20);
try {
    $image->insert($watermark, 'bottom-right', 10, 10);
    echo "success 1\n";
} catch (\Throwable $e) {
    echo "fail 1: " . $e->getMessage() . "\n";
}

try {
    $image = $manager->create(100, 100);
    $image->insert($watermark, 10, 10, 'bottom-right');
    echo "success 2\n";
} catch (\Throwable $e) {
    echo "fail 2: " . $e->getMessage() . "\n";
}

try {
    $image = $manager->create(100, 100);
    $image->place($watermark, 'bottom-right', 10, 10);
    echo "success 3\n";
} catch (\Throwable $e) {
    echo "fail 3: " . $e->getMessage() . "\n";
}
