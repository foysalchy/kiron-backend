<?php
require 'vendor/autoload.php';
$ref = new ReflectionMethod('Intervention\Image\Image', 'insert');
foreach($ref->getParameters() as $param) {
    $type = $param->getType();
    echo $param->getName() . ' (' . ($type ? $type->getName() : 'mixed') . ")\n";
}
