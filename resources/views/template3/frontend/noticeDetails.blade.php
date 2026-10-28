@extends('template3.layouts.front')

@section('meta')
    <title>{{ $notice->title }} - {{ $setup->shop_name ?? config('app.name') }}</title>
    <meta name="description" content="{{ Str::limit(strip_tags($notice->description), 160) }}">
    <meta property="og:title" content="{{ $notice->title }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($notice->description), 160) }}">
    @if($notice->image_url)
        <meta property="og:image" content="{{ $notice->image_url }}">
    @endif
@endsection

@section('content')
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
        <div class="mx-auto max-w-4xl">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 mb-6 text-sm text-gray-400">
                <a href="{{ route('home') }}" class="hover:text-[var(--primary-color)] transition-colors flex items-center gap-1">
                    <i class="fas fa-home text-xs"></i> {{ __('Home') }}
                </a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-gray-800 font-medium truncate max-w-[200px] md:max-w-none">{{ $notice->title }}</span>
            </nav>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="p-6 md:p-10 pb-4 md:pb-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 text-xs font-semibold text-amber-600 bg-amber-50 px-3 py-1 rounded-full w-max mb-3">
                        <i class="fas fa-bullhorn text-[11px]"></i>
                        <span>{{ __('Announcement') }}</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-tight mb-3">
                        {{ $notice->title }}
                    </h1>
                    <div class="flex items-center gap-4 text-xs text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <i class="far fa-calendar-alt text-amber-500"></i>
                            <span>{{ $notice->created_at ? $notice->created_at->format('d M, Y') : '' }}</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i class="far fa-clock text-amber-500"></i>
                            <span>{{ $notice->created_at ? $notice->created_at->diffForHumans() : '' }}</span>
                        </span>
                    </div>
                </div>

                <div class="p-6 md:p-10">
                    @if ($notice->image_url)
                        <div class="mb-8 rounded-xl overflow-hidden border border-gray-100 bg-gray-50">
                            <img src="{{ $notice->image_url }}" alt="{{ $notice->title }}" class="w-full max-h-[500px] object-contain mx-auto">
                        </div>
                    @endif

                    <div class="notice-description text-gray-700 leading-relaxed text-[16px]">
                        {!! $notice->description !!}
                    </div>
                </div>

                <div class="p-6 md:p-10 pt-0 flex justify-between items-center border-t border-gray-100 mt-6 pt-6">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 transition">
                        <i class="fas fa-arrow-left text-xs"></i>
                        <span>{{ __('Back to Home') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <style>
        .notice-description h1,
        .notice-description h2,
        .notice-description h3,
        .notice-description h4 {
            font-weight: 700;
            color: #111827;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .notice-description p {
            margin-bottom: 1.25rem;
        }
        .notice-description ul {
            list-style-type: disc;
            margin-left: 1.5rem;
            margin-bottom: 1.25rem;
        }
        .notice-description ol {
            list-style-type: decimal;
            margin-left: 1.5rem;
            margin-bottom: 1.25rem;
        }
        .notice-description img {
            border-radius: 0.5rem;
            max-width: 100%;
            height: auto;
            margin: 1rem 0;
        }
    </style>
@endsection
