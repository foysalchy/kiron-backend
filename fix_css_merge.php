<?php
// 1. Edit app.js
$appJsPath = 'resources/js/app.js';
$appJsContent = file_get_contents($appJsPath);
if (strpos($appJsContent, "import '../css/app.css';") === false) {
    file_put_contents($appJsPath, "import '../css/app.css';\n" . $appJsContent);
    echo "Updated app.js\n";
}

// 2. Edit vite.config.js
$viteConfigPath = 'vite.config.js';
$viteConfigContent = file_get_contents($viteConfigPath);
$viteConfigContent = str_replace(
    "input: ['resources/css/app.css', 'resources/js/app.js'],",
    "input: ['resources/js/app.js'],",
    $viteConfigContent
);
file_put_contents($viteConfigPath, $viteConfigContent);
echo "Updated vite.config.js\n";

// 3. Edit all blade files
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
        $newC = preg_replace("/@vite\(\['resources\/css\/app\.css',\s*'resources\/js\/app\.js'\]\)/", "@vite(['resources/js/app.js'])", $c);
        if ($c !== $newC) {
            file_put_contents($f, $newC);
            echo "Updated $f\n";
        }
    }
}
