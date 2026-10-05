<?php
$files = [
    'resources/views/template1/frontend/home.blade.php',
    'resources/views/template2/frontend/home.blade.php',
    'resources/views/template3/frontend/home.blade.php',
    'resources/views/template4/frontend/home.blade.php'
];

foreach ($files as $f) {
    if (file_exists($f)) {
        $c = file_get_contents($f);
        // We want to keep the block between <<<<<<< HEAD and =======
        // And remove the block between ======= and >>>>>>>
        $c = preg_replace('/<<<<<<< HEAD\r?\n(.*?)\r?\n=======\r?\n.*?\r?\n>>>>>>> [^\r\n]+/s', '$1', $c);
        file_put_contents($f, $c);
        echo "Resolved $f\n";
    }
}
