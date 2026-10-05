<?php
$files = glob('resources/views/template*/layouts/front.blade.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    $c = preg_replace("/@vite\(\['resources\/css\/app\.css',\s*'resources\/js\/app\.js'\]\)/", "@vite(['resources/js/app.js'])", $c);
    file_put_contents($f, $c);
    echo "Updated $f\n";
}
