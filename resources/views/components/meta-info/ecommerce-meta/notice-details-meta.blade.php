@include('components.meta-info.meta', [
    'setup' => $setup,
    'type' => 'Article',
    'title' => $notice->title,
    'description' => Str::limit(strip_tags($notice->description ?? ''), 160),
    'image' => $notice->image_url ?: ($setup->meta_image_url ?: $setup->logo_url),
    'canonical' => url()->current(),
    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => $notice->title,
            'url' => url()->current(),
        ],
    ],
])

