<?php
require 'vendor/autoload.php';
$ref = new ReflectionMethod('Intervention\Image\Image', 'insert');
echo $ref->getFileName() . ':' . $ref->getStartLine() . "\n";
