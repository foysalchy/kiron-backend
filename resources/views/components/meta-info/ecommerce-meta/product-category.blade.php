@php
    $breadcrumbItems = [
        ['name' => 'Home', 'url' => url('/')],
    ];

    if (isset($megaCategory)) {
        $breadcrumbItems[] = ['name' => $megaCategory->name, 'url' => url($megaCategory->slug)];
    }

    if (isset($subCategory)) {
        $breadcrumbItems[] = ['name' => $subCategory->name, 'url' => url($subCategory->slug)];
    }

    if (isset($miniCategory)) {
        $breadcrumbItems[] = ['name' => $miniCategory->name, 'url' => url($miniCategory->slug)];
    }

    $breadcrumbItems[] = [
        'name' => $category->name,
        'url'  => url()->current(),
    ];
@endphp
@include('components.meta-info.meta', [
    'setup' => $setup,

    'type' => 'CollectionPage',

    'title' => $category->meta_title ?: $category->name . ' | ' . ($setup->shop_name ?? ''),

    'description' => $category->meta_description ?: 'Browse our latest collection of ' . $category->name,

    'keywords' => $category->meta_keywords ?: $category->name . ', online shop, ' . ($setup->shop_name ?? ''),

    'image' => $category->image ? asset('storage/' . $category->image) : asset('images/default-share.jpg'),

    'canonical' => url()->current(),

    'breadcrumb' => $breadcrumbItems,

])
