<?php
$files = glob('resources/views/template*/frontend/home.blade.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    
    $preloadString = <<<'HTML'
@section('meta')
    @include('components.meta-info.ecommerce-meta.index-meta', ['setup' => $setup])
    @if(isset($mainSliders) && $mainSliders->isNotEmpty())
        @php $firstSlider = $mainSliders->first(); @endphp
        <link rel="preload" as="image" href="{{ $firstSlider->mobile_image_url ?? asset('images/template1/frontend/cover.webp') }}" media="(max-width: 767px)">
        <link rel="preload" as="image" href="{{ $firstSlider->image_url ?? asset('images/template1/frontend/cover.webp') }}" media="(min-width: 768px)">
    @endif
@endsection
HTML;

    // Check if meta section exists
    if (preg_match("/@section\('meta'\).*?@endsection/s", $c)) {
        $c = preg_replace("/@section\('meta'\).*?@endsection/s", $preloadString, $c);
        file_put_contents($f, $c);
        echo "Updated $f\n";
    }
}
