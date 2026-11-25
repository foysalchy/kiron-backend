@extends('saas.layouts.layout')

@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::FEATURE,
        $setup->company_id ?? null
    );
@endphp

@include('components.meta-info.saas-meta', [
    'setup' => $setup,

    'type' => 'CollectionPage',

    'title' => $pageData?->meta_title ?? ('Features | ' . ($setup->shop_name ?? 'Dorja')),

    'description' => $pageData?->meta_description ?? 'Explore all features of our ERP, POS, Inventory, CRM, Accounting, HRM and Business Management Software.',

    'keywords' => $pageData?->meta_keywords ? (is_array($pageData->meta_keywords) ? implode(',', $pageData->meta_keywords) : $pageData->meta_keywords) : 'ERP Features, POS Features, Inventory Features, CRM Features',

    'image' => $pageData?->meta_image ?? asset('storage/' . ($setup->logo ?? '')),

    'canonical' => route('saas.feature.list'),

    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/')
        ],
        [
            'name' => 'Features',
            'url' => route('saas.feature.list')
        ]
    ]
])
@section('content')
    <!-- HEADER SECTION -->
    <section class="hero-bg relative pt-28 md:pt-36 pb-10 md:pb-14 overflow-hidden">
        <div class="container mx-auto px-6 text-center relative z-10">
            <h1 class="move-up text-[#0A1F22] text-4xl md:text-6xl font-black mb-6">Our Features</h1>
            <p class="text-[#425B5F] text-lg md:text-xl max-w-2xl mx-auto">
                Stay updated with the latest trends, tips, and guides on business growth, modern technology, and e-commerce
                solutions.
            </p>
        </div>
    </section>

    <!-- FEATURES GRID SECTION -->
    <section class="py-8 md:py-14 px-4 md:px-10">
        <div class="container mx-auto max-w-[1240px]">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($allFeatures as $feature)
                    <a href="{{ route('saas.feature.details', $feature->slug) }}"
                        class="glass flex items-center gap-4 p-5 group rounded-2xl border border-white/80 lift transition-all duration-300">

                        <!-- Left Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 rounded-xl bg-white/70 border border-white/80 flex items-center justify-center text-[#00555C]">
                                @if ($feature->image_url)
                                    <img src="{{ $feature->image_url ?? asset('images/saas/live1.png') }}" class="w-10 h-10 object-contain"
                                        alt="{{ $feature->title }}" />
                                @else
                                    <i class="{{ $feature->icon ?? 'fa-solid fa-file-lines' }} text-xl text-[#00555C]"></i>
                                @endif
                            </div>
                        </div>

                        <!-- Right Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="block truncate text-lg font-semibold text-[#0A1F22] group-hover:text-[#00555C] transition-colors">
                                    {{ $feature->title }}
                                </span>
                                <span aria-hidden="true" style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #8AA3A0;" class="shrink-0 font-medium">
                                    {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>

                            <span class="inline-flex items-center gap-1.5 font-semibold text-xs text-[#00555C]">
                                <span>Read More</span>
                                <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                            </span>
                        </div>

                    </a>
                @endforeach
            </div>

            <div class="mt-16">
                {{ $allFeatures->links() }}
            </div>
            @if (!empty($pageData?->description))
                <div class="mt-5 glass rounded-3xl border border-white/80 px-8 py-8 prose prose-slate w-full min-w-full">
                    {!! $pageData->description !!}
                </div>
            @endif
        </div>
    </section>
@endsection