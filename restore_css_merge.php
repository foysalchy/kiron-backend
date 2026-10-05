<?php
// 1. Restore app.js
$appJsPath = 'resources/js/app.js';
$appJsContent = file_get_contents($appJsPath);
$appJsContent = str_replace("import '../css/app.css';\n", "", $appJsContent);
file_put_contents($appJsPath, $appJsContent);
echo "Restored app.js\n";

// 2. Restore vite.config.js
$viteConfigPath = 'vite.config.js';
$viteConfigContent = file_get_contents($viteConfigPath);
$viteConfigContent = str_replace(
    "input: ['resources/js/app.js'],",
    "input: ['resources/css/app.css', 'resources/js/app.js'],",
    $viteConfigContent
);
$viteConfigContent = preg_replace("/\s*build: \{\s*cssCodeSplit: false,\s*\},\s*/s", "\n", $viteConfigContent);
file_put_contents($viteConfigPath, $viteConfigContent);
echo "Restored vite.config.js\n";

// 3. Restore all blade files
$bladeFiles = [
    'resources/views/landing/landing1.blade.php',
    'resources/views/landing/landing2.blade.php',
    'resources/views/landing/landing3.blade.php',
    'resources/views/saas/layouts/layout.blade.php',
    'resources/views/template1/layouts/front.blade.php',
    'resources/views/template2/layouts/front.blade.php',
    'resources/views/template3/layouts/front.blade.php',
    'resources/views/template4/layouts/front.blade.php',
    'resources/views/template5/layouts/front.blade.php',
    'resources/views/welcome.blade.php'
];

foreach ($bladeFiles as $f) {
    if (file_exists($f)) {
        $c = file_get_contents($f);
        $newC = preg_replace("/@vite\(\['resources\/js\/app\.js'\]\)/", "@vite(['resources/css/app.css', 'resources/js/app.js'])", $c);
        if ($c !== $newC) {
            file_put_contents($f, $newC);
            echo "Restored $f\n";
        }
    }
}
