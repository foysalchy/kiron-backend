@props([
'name' => '',
'isDummy' => false,
'url' => null,
])

@php
$adminBaseUrl = config('app.admin_url', 'https://app.dorja.io');

$routesMap = [
'Notice' => '/content/notices',
'Slider' => '/content/sliders',
'Menu Setting' => '/settings/menu',
'Categories' => '/products/mega-categories',
'Product Group' => '/group/products',
'Products' => '/products',
'Brand' => '/products/brands',
'Reviews' => '#',
'Knowledge Base' => '/support/kb',
'Shipping Charge Rule' => '/site-settings',
'Content Setting' => '/settings/content',
'Footer Features' => '/settings/content?tab=all_page',
'Payment Gateways' => '/settings/content?tab=footer_bottom_right',
'Product Custom Block' => '/settings/content?tab=product_page',
'Social Setting' => '/social-settings',
'SEO & Content' => '/settings/meta',
'Pages' => '/content/pages',
'Blog' => '/content/blogs',
'Contact Messages' => '/contact-messages',
'Site Setting' => '/site-settings',
];

$targetUrl = $url ?: ($routesMap[$name] ? rtrim($adminBaseUrl, '/') . $routesMap[$name] : rtrim($adminBaseUrl, '/'));
@endphp

@if(!empty($activePreviewTheme))
<a href="{{ $targetUrl }}" target="_blank" rel="noopener noreferrer"
    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 shadow-2xs select-none my-1 hover:bg-purple-100 hover:border-purple-300 transition-all cursor-pointer group"
    title="Open in Admin Panel: {{ $name }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z" />
        <path d="M7 7h.01" />
    </svg>
    <span>{{ $name }}</span>
    @if($isDummy)
    <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[10px] font-bold border border-amber-300">
        Sample Preview
    </span>
    @endif
    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-purple-400 group-hover:text-purple-700 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
        <polyline points="15 3 21 3 21 9" />
        <line x1="10" y1="14" x2="21" y2="3" />
    </svg>
</a>
@endif