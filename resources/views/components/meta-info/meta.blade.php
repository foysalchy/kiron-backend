@props(['title' => null, 'description' => null, 'keywords' => null, 'image' => null])

@php
    $displayTitle = $title ?? ($setup->title ?? ($setup->shop_name ?? 'Home'));
    $displayDesc = $description ?? ($setup->description ?? $setup->shop_name . ' - Secure investment opportunities.');
    $displayKeywords = $keywords ?? ($setup->tags ?? '');

    $shareImage = asset('images/header/logo.svg');
    if (isset($image) && $image) {
        $shareImage = $image;
    } elseif (isset($setup->meta_image) && $setup->meta_image) {
        $shareImage = asset('storage/' . $setup->meta_image);
    } elseif (isset($setup->logo) && $setup->logo) {
        $shareImage = asset('storage/' . $setup->logo);
    }

    $socialUrls = isset($socialLinks) ? $socialLinks->pluck('url')->toArray() : [];

    $schema = [
        'page' => [
            'title' => $displayTitle,
            'description' => $displayDesc,
            'keywords' => $displayKeywords,
            'canonical' => url()->current(),
        ],
        'openGraph' => [
            'type' => 'website',
            'title' => $displayTitle,
            'description' => $displayDesc,
            'url' => url()->current(),
            'site_name' => $setup->shop_name ?? 'Tulip Valley',
            'image' => $shareImage,
            'locale' => 'en_US',
        ],
        'twitter' => [
            'card' => 'summary_large_image',
            'title' => $displayTitle,
            'description' => $displayDesc,
            'image' => $shareImage,
        ],
        'breadcrumb' => [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')]],
        ],
        'organization' => [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => $setup->shop_name ?? 'Bhaiya Digital',
                    'url' => url('/'),
                    'logo' => $setup->logo ? asset('storage/' . $setup->logo) : asset('images/header/logo.svg'),
                    'sameAs' => $socialUrls,
                    'founder' => [
                        '@type' => 'Person',
                        'name' => $setup->founder_name ?? 'Maroof Sattar Ali',
                        'jobTitle' => $setup->founder_designation ?? 'Chairman',
                    ],
                    'parentOrganization' => ['@type' => 'Organization', 'name' => 'Bhaiya Group'],
                    'foundingDate' => $setup->established
                        ? \Carbon\Carbon::parse($setup->established)->format('Y')
                        : '1972',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => strip_tags(
                            $setup->store_address ?? ($setup->corporate_address ?? 'Dhaka, BD'),
                        ),
                        'addressLocality' => 'Dhaka',
                        'addressCountry' => 'BD',
                    ],
                ],
            ],
        ],
    ];
@endphp

{{-- HTML Meta Tags --}}
<meta name="description" content="{{ $schema['page']['description'] }}">
<meta name="keywords" content="{{ $schema['page']['keywords'] }}">
<link rel="canonical" href="{{ $schema['page']['canonical'] }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $schema['openGraph']['type'] }}">
<meta property="og:title" content="{{ $schema['openGraph']['title'] }}">
<meta property="og:description" content="{{ $schema['openGraph']['description'] }}">
<meta property="og:url" content="{{ $schema['openGraph']['url'] }}">
<meta property="og:site_name" content="{{ $schema['openGraph']['site_name'] }}">
<meta property="og:image" content="{{ $schema['openGraph']['image'] }}">
<meta property="og:locale" content="{{ $schema['openGraph']['locale'] }}">

{{-- Twitter --}}
<meta name="twitter:card" content="{{ $schema['twitter']['card'] }}">
<meta name="twitter:title" content="{{ $schema['twitter']['title'] }}">
<meta name="twitter:description" content="{{ $schema['twitter']['description'] }}">
<meta name="twitter:image" content="{{ $schema['twitter']['image'] }}">

{{-- JSON-LD Scripts (অরিজিনাল ব্রেডক্রাম্ব এবং অর্গানাইজেশন গ্রাফ) --}}
<script type="application/ld+json">{!! json_encode($schema['breadcrumb']) !!}</script>
<script type="application/ld+json">{!! json_encode($schema['organization']) !!}</script>
