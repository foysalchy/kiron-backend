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

    // 1. FontAwesome: defer loading
    $content = preg_replace_callback('/<link[^>]*href="[^"]*font-awesome[^"]*"[^>]*>/i', function($matches) {
        $tag = $matches[0];
        if (strpos($tag, 'media="print"') !== false || strpos($tag, 'preload') !== false) {
            return $tag;
        }
        return str_replace('>', ' media="print" onload="this.media=\'all\'">', $tag);
    }, $content);

    // 2. Add loading="lazy", width="800", height="800" to img tags
    $content = preg_replace_callback('/<img\s+([^>]+)>/i', function($matches) {
        $attrs = $matches[1];
        
        $needs_lazy = stripos($attrs, 'loading=') === false && stripos($attrs, 'fetchpriority="high"') === false;
        $needs_width = stripos($attrs, 'width=') === false;
        $needs_height = stripos($attrs, 'height=') === false;
        
        $new_attrs = $attrs;
        if ($needs_lazy) {
            $new_attrs .= ' loading="lazy"';
        }
        if ($needs_width) {
            $new_attrs .= ' width="800"';
        }
        if ($needs_height) {
            $new_attrs .= ' height="800"';
        }
        
        return "<img " . trim($new_attrs) . ">";
    }, $content);

    if ($content !== $original) {
        file_put_contents($filepath, $content);
        echo "Updated: $filepath\n";
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
