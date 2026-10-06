@extends('template3.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.blog-meta', ['setup' => $setup])
@endsection
@section('content')
    <!-- 1. Hero & Search -->
    <section class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 py-10 md:py-24">
        <div class="container mx-auto px-4 text-center text-primary">
            <h1 class="text-2xl md:text-5xl font-bold mb-3 tracking-tight">Our Blog</h1>
            <p class="text-lg md:text-xl mb-8 opacity-90">Latest insights, styles, and shopping tips just for you</p>

            <form action="{{ url()->current() }}" method="GET" class="max-w-2xl mx-auto relative">
                @if (request('tag'))
                    <input type="hidden" name="tag" value="{{ request('tag') }}">
                @endif
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..."
                    class="w-full pl-12 pr-6 py-4 rounded-xl text-gray-800 bg-white shadow-2xl outline-none focus:ring-4 focus:ring-white/20 transition-all">
            </form>
        </div>
    </section>

    <!-- 3. Blog Grid -->
    <section class="container mx-auto py-6 px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            @forelse($blogs as $blog)
                <div
                    class="relative bg-white rounded-lg border border-gray-100 overflow-hidden shadow-sm hover:shadow-xs transition-all duration-300 group">

                    <div class="relative aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
                        @if ($blog->thumbnail_url)
                            <img src="{{ $blog->thumbnail_url }}" alt="{{ $blog->title }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <img src="{{ asset('./images/template1/frontend/default.webp') }}" alt="Default Image"
                                class="w-full h-full object-cover" loading="lazy" width="800" height="800">
                        @endif
                    </div>

                    <!-- Card Content -->
                    <div class="p-6">
                        <h2
                            class="text-xl font-bold text-gray-900 mb-3 leading-tight line-clamp-2 group-hover:text-primary transition-colors">
                            {{ $blog->title }}
                        </h2>
                        <p class="text-gray-700 text-sm mb-6 line-clamp-2">
                            {{ $blog->short ?? \Illuminate\Support\Str::limit(strip_tags($blog->body), 100) }}
                        </p>

                        <!-- Metadata -->
                        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-gray-500 mb-5 font-medium pb-4">
                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1.5"><i class="fa-regular fa-user text-sm"></i>
                                    {{ $blog->user->name ?? 'Admin' }} </span>
                                <span class="flex items-center gap-1.5"><i class="fa-regular fa-calendar text-sm"></i>
                                    {{ $blog->created_at->format('Y-m-d') }}</span>
                            </div>
                            <span class="text-secondary font-bold">
                                {{ $blog->reading_time }} Minute
                            </span>
                        </div>

                        <!-- Read More Button (Main Link) -->
                        <a href="{{ url($blog->slug) }}"
                            class="after:absolute after:inset-0 after:z-10 block w-full text-center primary-bg hover:bg-blue-600 text-primary font-bold py-3 rounded-lg transition-all duration-300 text-sm">
                            Read More <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </a>
                        {{-- <a href="{{ url($landing->slug) }}" class="btn btn-primary">landing</a> --}}
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-20 bg-white rounded-lg border border-dashed">
                    <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png"
                        class="w-24 h-24 mx-auto opacity-10 mb-4" loading="lazy" width="800" height="800">
                    <h2 class="text-xl font-bold text-gray-400">No blogs found in this category.</h2>
                </div>
            @endforelse

        </div>

        <!-- Pagination (Designed to match the "Show Results" bar) -->
        <div class="mt-12 flex flex-col items-center gap-4">
            <p class="text-sm text-gray-500">Showing {{ $blogs->firstItem() }} to {{ $blogs->lastItem() }} of
                {{ $blogs->total() }} results</p>
            <div class="flex justify-center">
                {{ $blogs->links() }}
            </div>
        </div>
    </section>
@endsection
