<?php
$files = glob('resources/views/template*/frontend/home.blade.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    
    // Replace the old preload strings with the new ones that include fetchpriority="high"
    $c = str_replace(
        '<link rel="preload" as="image" href="{{ $firstSlider->mobile_image_url ?? asset(\'images/template1/frontend/cover.webp\') }}" media="(max-width: 767px)">',
        '<link rel="preload" as="image" href="{{ $firstSlider->mobile_image_url ?? asset(\'images/template1/frontend/cover.webp\') }}" media="(max-width: 767px)" fetchpriority="high">',
        $c
    );
    
    $c = str_replace(
        '<link rel="preload" as="image" href="{{ $firstSlider->image_url ?? asset(\'images/template1/frontend/cover.webp\') }}" media="(min-width: 768px)">',
        '<link rel="preload" as="image" href="{{ $firstSlider->image_url ?? asset(\'images/template1/frontend/cover.webp\') }}" media="(min-width: 768px)" fetchpriority="high">',
        $c
    );
    
    file_put_contents($f, $c);
    echo "Updated $f\n";
}
