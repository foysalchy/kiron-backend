<?php
$files = [
    'resources/views/welcome.blade.php',
    'resources/views/template5/layouts/front.blade.php',
    'resources/views/template4/layouts/front.blade.php',
    'resources/views/template3/layouts/front.blade.php',
    'resources/views/template1/layouts/front.blade.php',
    'resources/views/saas/layouts/layout.blade.php',
    'resources/views/landing/landing3.blade.php',
    'resources/views/landing/landing2.blade.php',
    'resources/views/landing/landing1.blade.php',
];
foreach ($files as $f) {
    if (file_exists($f)) {
        $c = file_get_contents($f);
        $c = str_replace("@vite(['resources/css/app.css', 'resources/js/app.js'])", "@vite(['resources/css/app.css', 'resources/js/app.js'])", $c);
        file_put_contents($f, $c);
    }
}
echo "Done\n";
