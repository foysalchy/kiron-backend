<?php
require 'vendor/autoload.php';
$ref = new ReflectionMethod('Intervention\Image\Image', 'insert');
echo "insert signature:\n";
foreach($ref->getParameters() as $param) {
    echo $param->getName() . "\n";
}

$ref = new ReflectionMethod('Intervention\Image\Image', 'scaleDown');
echo "\nscaleDown signature:\n";
foreach($ref->getParameters() as $param) {
    echo $param->getName() . "\n";
}
