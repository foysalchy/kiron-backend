@extends('template2.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.page-meta', ['page' => $page, 'setup' => $setup])
@endsection

@section('content')
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">

        <!-- Page Container -->
        <div class="mx-auto">

            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 mb-8 text-sm text-gray-400">
                <a href="{{ route('home') }}" class="hover:text-[var(--primary-color)] transition-colors flex items-center gap-1">
                    <i class="fas fa-home text-xs"></i> Home
                </a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-gray-800 font-medium">{{ $page->title }}</span>
            </nav>

            <!-- Main Content Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">

                <!-- Header -->
                <div class="p-8 md:p-12 pb-0 md:pb-0">
                    <!-- <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">
                        {{ $page->title }}
                    </h1> -->
                  
                </div>

                <!-- Body -->
                <div class="p-8 md:p-12 mt-0 md:pt-0 pt-0">
                    @if ($page->image_url)
                        <img src="{{ $page->image_url }}" class="w-full h-auto rounded-xl mb-8 shadow-sm">
                    @endif

                    <div class="prose max-w-none leading-relaxed">
                        {!! $page->description !!}
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
