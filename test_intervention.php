<?php
require 'vendor/autoload.php';
$manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
$image = $manager->create(100, 100);
echo 'place: ' . (method_exists($image, 'place') ? 'yes' : 'no') . "\n";
echo 'insert: ' . (method_exists($image, 'insert') ? 'yes' : 'no') . "\n";
