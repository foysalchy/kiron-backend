<?php
require 'vendor/autoload.php';
$ref = new ReflectionMethod('Intervention\Image\Image', 'insert');
foreach($ref->getParameters() as $param) {
    echo $param->getName() . ' (' . ($param->isDefaultValueAvailable() ? var_export($param->getDefaultValue(), true) : 'required') . ")\n";
}
