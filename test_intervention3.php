<?php
require 'vendor/autoload.php';
$manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
$image = $manager->decodePath('public/favicon.ico');
$ref = new ReflectionClass($image);
foreach($ref->getMethods() as $method) {
    echo $method->getName() . "\n";
}
