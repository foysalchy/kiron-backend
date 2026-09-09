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
$rawTitle = trim((string) $title);
$title = (!empty($rawTitle) && !in_array(strtolower($rawTitle), ['null', 'undefined'])) ? $rawTitle : ($setup->title ?? $setup->shop_name ?? '');

$rawDesc = trim((string) $description);
$description = (!empty($rawDesc) && !in_array(strtolower($rawDesc), ['null', 'undefined'])) ? $rawDesc : ($setup->description ?? '');
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
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

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

<?php
$graph = [];

$graph[] = [
    '@type' => 'OnlineStore',
    'name' => html_entity_decode($setup->shop_name ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8'),
    'url' => url('/'),
    'logo' => $organizationLogo,
    'email' => $setup->email ?? '',
    'legalName' => html_entity_decode($setup->legal_name ?? ($setup->shop_name ?? ''), ENT_QUOTES | ENT_XML1, 'UTF-8'),
    'alternateName' => !empty($setup->alternate_name) ? array_map(fn($v) => html_entity_decode(trim($v), ENT_QUOTES | ENT_XML1, 'UTF-8'), explode(',', $setup->alternate_name)) : [],
    'telephone' => $setup->phone ?? '',
    'foundingDate' => $setup->established ?? '',
    'sameAs' => $socialLinks,
    'founder' => [
        '@type' => 'Person',
        'name' => html_entity_decode($setup->founder_name ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8'),
        'jobTitle' => html_entity_decode($setup->founder_designation ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8')
    ],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => html_entity_decode(($setup?->store_address ?: $setup?->corporate_address) ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8'),
        'addressCountry' => 'BD'
    ]
];

$graph[] = [
    '@type' => 'WebSite',
    'name' => html_entity_decode($setup->shop_name ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8'),
    'url' => url('/')
];

$graph[] = [
    '@type' => 'WebPage',
    'name' => html_entity_decode($title, ENT_QUOTES | ENT_XML1, 'UTF-8'),
    'description' => html_entity_decode($description, ENT_QUOTES | ENT_XML1, 'UTF-8'),
    'url' => $canonical,
    'primaryImageOfPage' => $image
];

if (count($breadcrumb)) {
    $graph[] = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($breadcrumb)->values()->map(function ($item, $index) {
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => html_entity_decode($item['name'], ENT_QUOTES | ENT_XML1, 'UTF-8'),
                'item' => $item['url']
            ];
        })->toArray()
    ];
}

if ($type == 'BlogPosting') {
    $graph[] = [
        '@type' => 'BlogPosting',
        'headline' => html_entity_decode($schema['headline'] ?? $title, ENT_QUOTES | ENT_XML1, 'UTF-8'),
        'description' => html_entity_decode($description, ENT_QUOTES | ENT_XML1, 'UTF-8'),
        'image' => [
            '@type' => 'ImageObject',
            'url' => $image
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $canonical
        ],
        'author' => [
            '@type' => 'Person',
            'name' => html_entity_decode($schema['author'] ?? ($setup->founder_name ?? ''), ENT_QUOTES | ENT_XML1, 'UTF-8')
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => html_entity_decode($setup->shop_name ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $organizationLogo
            ]
        ],
        'datePublished' => isset($schema['published'])
            ? \Carbon\Carbon::parse($schema['published'])->toIso8601String()
            : now()->toIso8601String(),
        'dateModified' => isset($schema['updated'])
            ? \Carbon\Carbon::parse($schema['updated'])->toIso8601String()
            : now()->toIso8601String()
    ];
}

if ($type == 'ContactPage') {
    $graph[] = [
        '@type' => 'ContactPage',
        'name' => html_entity_decode($title, ENT_QUOTES | ENT_XML1, 'UTF-8'),
        'description' => html_entity_decode($description, ENT_QUOTES | ENT_XML1, 'UTF-8'),
        'url' => $canonical
    ];
}

if ($type == 'FAQPage' && count($faq)) {
    $graph[] = [
        '@type' => 'FAQPage',
        'mainEntity' => collect($faq)->map(function ($item) {
            return [
                '@type' => 'Question',
                'name' => html_entity_decode(data_get($item, 'question') ?? data_get($item, 'title'), ENT_QUOTES | ENT_XML1, 'UTF-8'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => html_entity_decode(strip_tags(data_get($item, 'answer') ?? data_get($item, 'content')), ENT_QUOTES | ENT_XML1, 'UTF-8')
                ]
            ];
        })->toArray()
    ];
}

if ($type == 'Product') {
    $productSchema = [
        '@type' => 'Product',
        'name' => html_entity_decode($schema['name'] ?? $title, ENT_QUOTES | ENT_XML1, 'UTF-8'),
        'description' => html_entity_decode($description, ENT_QUOTES | ENT_XML1, 'UTF-8'),
        'image' => $image,
        'sku' => $schema['sku'] ?? null,
        'mpn' => $schema['mpn'] ?? $schema['sku'] ?? null,
        'brand' => [
            '@type' => 'Brand',
            'name' => html_entity_decode($schema['brand_name'] ?? $setup->shop_name ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8')
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => $canonical,
            'priceCurrency' => $schema['currency'] ?? 'BDT',
            'price' => $schema['price'] ?? 0,
            'availability' => $schema['availability'] ?? 'https://schema.org/InStock',
            'priceValidUntil' => now()->addYear()->format('Y-m-d'),
            'hasMerchantReturnPolicy' => [
                '@type' => 'MerchantReturnPolicy',
                'applicableCountry' => 'BD',
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                'merchantReturnDays' => 7,
                'returnMethod' => 'https://schema.org/ReturnByMail',
                'returnFees' => 'https://schema.org/FreeReturn'
            ],
            'shippingDetails' => [
                '@type' => 'OfferShippingDetails',
                'shippingRate' => [
                    '@type' => 'MonetaryAmount',
                    'value' => 0,
                    'currency' => $schema['currency'] ?? 'BDT'
                ],
                'shippingDestination' => [
                    '@type' => 'DefinedRegion',
                    'addressCountry' => 'BD'
                ],
                'deliveryTime' => [
                    '@type' => 'ShippingDeliveryTime',
                    'handlingTime' => [
                        '@type' => 'QuantitativeValue',
                        'minValue' => 0,
                        'maxValue' => 1,
                        'unitCode' => 'd'
                    ],
                    'transitTime' => [
                        '@type' => 'QuantitativeValue',
                        'minValue' => 1,
                        'maxValue' => 5,
                        'unitCode' => 'd'
                    ]
                ]
            ]
        ]
    ];
    
    if (isset($schema['review_count']) && $schema['review_count'] > 0 && isset($schema['rating_value'])) {
        $productSchema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => $schema['rating_value'],
            'reviewCount' => $schema['review_count'],
            'bestRating' => 5,
            'worstRating' => 1
        ];
    }
    
    $graph[] = $productSchema;
}
?>

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => $graph
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>