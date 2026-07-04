@extends('saas.layouts.layout')

@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::FEATURE,$setup->company_id ?? null
    );
@endphp

@include('components.meta-info.saas-meta', [
    'setup' => $setup,

    'type' => 'CollectionPage',

    'title' => $pageData->meta_title ?? ('Features | ' . $setup->shop_name),

    'description' => $pageData->meta_description ?? 'Explore all features of our ERP, POS, Inventory, CRM, Accounting, HRM and Business Management Software.',

    'keywords' => $pageData->meta_keywords ? implode(',', $pageData->meta_keywords) : 'ERP Features, POS Features, Inventory Features, CRM Features',

    'image' => $pageData->meta_image ?? asset('storage/' . $setup->logo),

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
    <section class="bg-[#34a487] pt-32 pb-20 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#34a487]/10 blur-[120px] rounded-full"></div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <h1 class="text-white text-4xl md:text-6xl font-black mb-6">Our Features</h1>
            <p class="text-gray-800 text-lg md:text-xl max-w-2xl mx-auto">
                Stay updated with the latest trends, tips, and guides on business growth, modern technology, and e-commerce solutions.
            </p>
        </div>
    </section>

    <!-- FEATURES GRID SECTION -->
    <section class="bg-white py-10">
        <div class="container mx-auto px-6 md:px-10">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                @foreach ($allFeatures as $feature)
                        <a href="{{ route('saas.feature.details', $feature->slug) }}"
            class="flex items-center gap-5 p-4 group rounded border border-gray-200 hover:border-[#34a487] transition-all duration-300">

            <!-- Left Icon -->
            <div class="flex-shrink-0">
                  <div
            class="w-12 h-12  bg-[#34a48721] rounded
                   border border-white/20
                   flex items-center justify-center">
            <i class="{{ $feature->icon ?? 'fa-solid fa-file-lines' }} text-2xl text-[#34a487]"></i>
        </div>
            </div>

            <!-- Right Content -->
            <div class="flex-1 min-w-0">
    <div class="block w-full truncate text-lg text-gray-900 group-hover:text-[#34a487]">
        {{ $feature->title }}
    </div>
 
               
 <!-- <p class="text-gray-700 text-lg leading-relaxed mb-12">
                                {{ $feature->subtitle ?? '' }}
                            </p> -->
                <span
                    class="inline-flex items-center gap-2 font-semibold text-[#34a487]">
                    Read More
                    <i
                        class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                </span>
            </div>

        </a>
                    @endforeach
            </div>

            <div class="mt-16">
                {{ $allFeatures->links() }}
            </div>
            <div class="mt-5">
            {!! $pageData->description!!}
        </div>
        </div>
    </section>
@endsection
