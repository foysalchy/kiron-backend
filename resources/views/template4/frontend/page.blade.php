@extends('template4.layouts.front')

@section('content')
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">

        <!-- Page Container -->
        <div class="mx-auto">

            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 mb-8 text-sm text-gray-400">
                <a href="{{ route('home') }}" class="hover:text-[var(--primary-color)]">Home</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-gray-800 font-medium">{{ $page->title }}</span>
            </nav>

            <!-- Main Content Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">

                <!-- Header -->
                <div class="p-8 md:p-12 pb-0 md:pb-0">
                    <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">
                        {{ $page->title }}
                    </h1>
                    <div class="w-16 h-1.5 bg-[var(--primary-color)] rounded-full"></div>
                </div>

                <!-- Body -->
                <div class="p-8 md:p-12 mt-0 md:pt-0 pt-0">
                    @if ($page->image)
                        <img src="{{ asset('storage/' . $page->image) ?? '' }}" loading="lazy" height="" width="" alt="image" class="w-full h-auto rounded-xl mb-8 shadow-sm">
                    @endif

                    <div class="prose prose-slate max-w-none
                                prose-p:text-gray-500 prose-p:text-md prose-p:leading-relaxed
                                prose-p:m-0
                                prose-strong:text-gray-700 prose-a:text-[var(--primary-color)]">
                           {!! $page->description !!}
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Styles to match the image typography -->
    <style>
        .page-description h2,
        .page-description h3 {
            font-weight: 800;
            color: #1D2128;
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .page-description p {
            margin-bottom: 1.2rem;
        }

        .page-description ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .page-description li {
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .page-description strong {
            color: #FF6A00;
        }
    </style>
@endsection
