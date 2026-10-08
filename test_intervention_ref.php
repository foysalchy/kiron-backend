<?php
require 'vendor/autoload.php';

$manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

$ref = new ReflectionClass($manager);
echo "ImageManager methods:\n";
foreach($ref->getMethods() as $method) {
    echo $method->getName() . "\n";
}

$imageClass = 'Intervention\Image\Image';
if (class_exists($imageClass)) {
    $ref2 = new ReflectionClass($imageClass);
    echo "\nImage methods:\n";
    foreach($ref2->getMethods() as $method) {
        if (in_array($method->getName(), ['place', 'insert', 'watermark', 'overlay'])) {
            echo $method->getName() . "\n";
        }
    }
}
