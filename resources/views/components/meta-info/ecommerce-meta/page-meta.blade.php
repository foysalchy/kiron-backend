@include('components.meta-info.meta', [
    'setup' => $setup,
    'type' => 'WebPage',
    'title' => ($page->meta_title ?? null) ?: $page->title,
    'description' => ($page->meta_description ?? null) ?: Str::limit(strip_tags($page->description ?? ''), 160),
    'keywords' => is_array($page->meta_keywords ?? null)
        ? implode(',', $page->meta_keywords)
        : ($page->meta_keywords ?? ''),
    'image' => $page->image_url ?: ($setup->meta_image_url ?: $setup->logo_url),
    'canonical' => url($page->slug),
    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => $page->title,
            'url' => url($page->slug),
        ],
    ],
])

