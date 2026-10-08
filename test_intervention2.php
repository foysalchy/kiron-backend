<?php
require 'vendor/autoload.php';
$manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
$image = $manager->read('public/favicon.ico');
echo 'place: ' . (method_exists($image, 'place') ? 'yes' : 'no') . "\n";
echo 'insert: ' . (method_exists($image, 'insert') ? 'yes' : 'no') . "\n";
