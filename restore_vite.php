<?php
$files = glob('resources/views/template*/layouts/front.blade.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    // Restore the old vite directive
    $c = preg_replace("/@vite\(\['resources\/js\/app\.js'\]\)/", "@vite(['resources/css/app.css', 'resources/js/app.js'])", $c);
    file_put_contents($f, $c);
    echo "Restored $f\n";
}
