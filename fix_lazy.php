<?php
$files = glob('resources/views/template*/frontend/home.blade.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    // Fix the broken arrow operator
    $c = str_replace('- loading="lazy">', '->', $c);
    file_put_contents($f, $c);
    echo "Fixed $f\n";
}
