<?php
$dir = new RecursiveDirectoryIterator('resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.blade\.php$/', RegexIterator::GET_MATCH);
foreach ($files as $file) {
    $f = $file[0];
    $c = file_get_contents($f);
    if (strpos($c, '- loading="lazy">') !== false) {
        $c = str_replace('- loading="lazy">', '->', $c);
        file_put_contents($f, $c);
        echo "Fixed $f\n";
    }
}
