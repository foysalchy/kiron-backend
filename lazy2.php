<?php
$files = glob('resources/views/template*/frontend/home.blade.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    // Replace all img tags that do NOT have loading="lazy", EXCEPT the ones with loading="eager" or fetchpriority="high"
    $c = preg_replace_callback('/<img\s+([^>]+)>/i', function($m) {
        $attrs = $m[1];
        if (stripos($attrs, 'loading=') === false && stripos($attrs, 'fetchpriority="high"') === false) {
            return '<img ' . $attrs . ' loading="lazy">';
        }
        return $m[0];
    }, $c);
    file_put_contents($f, $c);
    echo "Updated $f\n";
}
