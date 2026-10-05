<?php

$directories = [
    'c:\laragon\www\kiron-backend\resources\views\template1',
    'c:\laragon\www\kiron-backend\resources\views\template2',
    'c:\laragon\www\kiron-backend\resources\views\template3',
    'c:\laragon\www\kiron-backend\resources\views\template4',
    'c:\laragon\www\kiron-backend\resources\views\template5',
];

function fix_file($filepath) {
    $content = file_get_contents($filepath);
    $original = $content;

    // Fix corrupted object operator ->
    $content = str_replace('- loading="lazy" width="800" height="800">', '->', $content);
    $content = str_replace('- width="800" height="800">', '->', $content);
    $content = str_replace('- height="800">', '->', $content);
    $content = str_replace('- loading="lazy" width="800">', '->', $content);

    // Fix corrupted array operator =>
    $content = str_replace('= loading="lazy" width="800" height="800">', '=>', $content);
    $content = str_replace('= width="800" height="800">', '=>', $content);
    $content = str_replace('= height="800">', '=>', $content);
    $content = str_replace('= loading="lazy" width="800">', '=>', $content);

    // Also the script added width="800" and height="800" to legitimate tags without them, 
    // but doing so on an <img> tag without a proper regex may have left multiple > or similar issues if there were other `>` inside.
    // However, the main breakage reported is the `->` inside blade tags.
    // Let's also fix conditional operators like `> 0` -> `> 0` which might have become ` loading="lazy"... 0` 
    // Actually, `<img` doesn't usually contain `> 0`, it's inside `{{ }}`.
    // If it was `$stock > 0`, it would be `$stock loading="lazy"... 0`. 
    // Let's just fix the known ones first.

    if ($content !== $original) {
        file_put_contents($filepath, $content);
        echo "Fixed: $filepath\n";
    }
}

foreach ($directories as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            fix_file($file->getPathname());
        }
    }
}

echo "Done.\n";
