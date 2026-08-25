@props([
    'setup',
    'type' => 'WebPage',
    'title' => null,
    'description' => null,
    'keywords' => null,
    'image' => null,
    'canonical' => null,
    'breadcrumb' => [],
    'schema' => [],
    'faq' => [],
    'socialLinks' => [],
])

<?php
$titlex = $title ?: $setup->title ?? $setup->shop_name;
$title = $titlex . ' | dorja.io';
$description = $description ?: $setup->description ?? '';
$keywords = is_array($keywords) ? implode(',', $keywords) : ($keywords ?: $setup->tags ?? '');
$canonical = $canonical ?: url()->current();
if (!$image) {
    if (!empty($setup->meta_image)) {
        $image = asset('storage/' . $setup->meta_image);
    } elseif (!empty($setup->logo)) {
        $image = asset('storage/' . $setup->logo);
    } else {
        $image = asset('images/header/logo.svg');
    }
}
if ($socialLinks instanceof \Illuminate\Support\Collection) {
    $socialLinks = $socialLinks->pluck('url')->filter()->values()->toArray();
}
$organizationLogo = !empty($setup->logo) ? asset('storage/' . $setup->logo) : $image;
?>

<title>{{ $title }}</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="title" content="{{ $title }}">
<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
<meta name="description" content="{{ $description }}">
@if ($keywords)
    <meta name="keywords" content="{{ $keywords }}">
@endif

<meta name="author" content="{{ $setup->shop_name ?? ''}}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $type == 'BlogPosting' ? 'article' : 'website' }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:site_name" content="{{ $setup->shop_name ?? ''}}">
<meta property="og:locale" content="en_US">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

{{-- Organization --}}
<script type="application/ld+json">
{!! json_encode([
    '@context'=>'https://schema.org',
    '@type'=>'Organization',
    'name'=>$setup->shop_name ?? '',
    'url'=>url('/'),
    'logo'=>$organizationLogo,
    'email'=>$setup->email ?? '',
    'telephone'=>$setup->phone ?? '',
    'foundingDate'=>$setup->established ?? '',
    'sameAs'=>$socialLinks,
    'founder'=>[
        '@type'=>'Person',
        'name'=>$setup->founder_name ?? '',
        'jobTitle'=>$setup->founder_designation ?? ''
    ],
    'address'=>[
        '@type'=>'PostalAddress',
        'streetAddress'=> ($setup?->store_address ?: $setup?->corporate_address) ?? '',
        'addressCountry'=>'BD'
    ]
],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
</script>

{{-- Website --}}
<script type="application/ld+json">
{!! json_encode([
    '@context'=>'https://schema.org',
    '@type'=>'WebSite',
    'name'=>$setup->shop_name ?? '',
    'url'=>url('/')
],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
</script>

{{-- WebPage --}}
<script type="application/ld+json">
{!! json_encode([
    '@context'=>'https://schema.org',
    '@type'=>'WebPage',
    'name'=>$title,
    'description'=>$description,
    'url'=>$canonical,
    'primaryImageOfPage'=>$image
],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
</script>

{{-- Breadcrumb --}}
@if (count($breadcrumb))
    <script type="application/ld+json">
{!! json_encode([
    '@context'=>'https://schema.org',
    '@type'=>'BreadcrumbList',
    'itemListElement'=>collect($breadcrumb)->values()->map(function($item,$index){

        return [
            '@type'=>'ListItem',
            'position'=>$index+1,
            'name'=>$item['name'],
            'item'=>$item['url']
        ];

    })->toArray()
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}
</script>
@endif


{{-- BlogPosting --}}
@if ($type == 'BlogPosting')
    <script type="application/ld+json">
{!! json_encode([
    '@context'=>'https://schema.org',
    '@type'=>'BlogPosting',
    'headline'=>$schema['headline'] ?? $title,
    'description'=>$description,
    'image'=>[
        '@type'=>'ImageObject',
        'url'=>$image
    ],
    'mainEntityOfPage'=>[
        '@type'=>'WebPage',
        '@id'=>$canonical
    ],
    'author'=>[
        '@type'=>'Person',
        'name'=>$schema['author'] ?? ($setup->founder_name ?? '')
    ],
    'publisher'=>[
        '@type'=>'Organization',
        'name'=>$setup->shop_name,
        'logo'=>[
            '@type'=>'ImageObject',
            'url'=>$organizationLogo
        ]
    ],
    'datePublished'=>isset($schema['published'])
        ? \Carbon\Carbon::parse($schema['published'])->toIso8601String()
        : now()->toIso8601String(),
    'dateModified'=>isset($schema['updated'])
        ? \Carbon\Carbon::parse($schema['updated'])->toIso8601String()
        : now()->toIso8601String()
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}
</script>
@endif


@if ($type == 'ContactPage')
    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ContactPage',
    'name' => $title,
    'description' => $description,
    'url' => $canonical
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif


@if ($type == 'FAQPage' && count($faq))
    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',

    'mainEntity' => collect($faq)->map(function ($item) {

        return [
            '@type' => 'Question',

            'name' => data_get($item, 'question')
                ?? data_get($item, 'title'),

            'acceptedAnswer' => [
                '@type' => 'Answer',

                'text' => strip_tags(
                    data_get($item, 'answer')
                    ?? data_get($item, 'content')
                )
            ]

        ];

    })->toArray()

], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

{{-- Product --}}
@if ($type == 'Product')
    <script type="application/ld+json">
{!! json_encode([
    '@context'=>'https://schema.org',
    '@type'=>'Product',

    'name'=>$schema['name'] ?? $title,

    'description'=>$description,

    'image'=>$image,

    'sku'=>$schema['sku'] ?? null,

    'brand'=>[
        '@type'=>'Brand',
        'name'=>$setup->shop_name
    ],

    'offers'=>[
        '@type'=>'Offer',

        'url'=>$canonical,

        'priceCurrency'=>$schema['currency'] ?? 'BDT',

        'price'=>$schema['price'] ?? 0,

        'availability'=>$schema['availability'] ?? 'https://schema.org/InStock'
    ]

],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) !!}
</script>
@endif
