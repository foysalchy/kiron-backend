@extends('template3.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.blog-details-meta', ['setup' => $setup])
@endsection
@section('content')
    <section class="container mx-auto py-4 md:py-6 px-4 lg:px-0">

        <nav
            class="flex items-center space-x-2 text-sm text-gray-500 mb-6 overflow-x-auto whitespace-nowrap pb-2 no-scrollbar">
            {{-- Home Link --}}
            <a href="/" class="hover:text-primary transition-colors flex items-center gap-1">
                <i class="fas fa-home text-xs"></i> Home
            </a>

            {{-- Blogs Index Link --}}
            <i class="fas fa-chevron-right text-[8px] opacity-40"></i>
            <a href="{{ url('/blogs') }}" class="hover:text-primary transition-colors">
                Blogs
            </a>

            {{-- Current Blog Title --}}
            <i class="fas fa-chevron-right text-[8px] opacity-40"></i>
            <span class="text-secondary font-bold truncate max-w-[200px] md:max-w-none" title="{{ $blog->title }}">
                {{ $blog->title }}
            </span>
        </nav>

        <!-- আপনার বিদ্যমান Back Button -->
        <a class="inline-flex items-center text-secondary hover:text-primary mb-6 font-medium" href="{{ url('/blogs') }}">
            <i class="fas fa-arrow-left mr-2 text-sm"></i> Back to blogs
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left: Main Content -->
            <div class="lg:col-span-2">
                <div class="rounded-lg border border-gray-200 bg-white shadow-xs overflow-hidden">

                    <!-- Featured Image: Dynamic -->
                    <div class="relative">
                        @if (!empty($blog->images) && isset($blog->images[0]))
                            <img src="{{ asset('storage/' . $blog->images[0]) }}" alt="{{ $blog->title }}"
                                class="w-full h-64 md:h-[450px] object-cover">
                        @else
                            <div class="w-full h-64 md:h-[450px] bg-gray-100 flex items-center justify-center text-gray-300">
                                <img src="{{ asset('./images/template1/frontend/default.webp') }}" alt="{{ $blog->title }}"
                                    class="w-full h-64 md:h-[450px] object-cover">
                            </div>
                        @endif
 
                    </div>

                    <div class="p-4 md:p-10">

                      

                        <div class="flex  items-center gap-6 text-sm text-gray-500  mb-4 justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-regular fa-user   text-black"></i> Author:
                                <span>{{ $blog->user->name ?? 'Admin' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fa-regular fa-calendar text-secondary"></i>
                                <span>{{ $blog->created_at->format('Y-m-d') }}</span>
                            </div>
                           
                        </div>
                   
                          <h1 class="text-xl md:text-3xl font-black text-gray-900 mb-4 md:mb-6 leading-tight">
                            {{ $blog->title }}
                        </h1>

                        <div class="prose prose-orange max-w-none"> {{-- Customize the prose style to match your theme:
                            prose-orange or prose-slate --}}
                            {!! $blog->body !!}

                            @if ($blog->body_2)
                                <div class="mt-6">{!! $blog->body_2 !!}</div>
                            @endif

                            @if ($blog->body_3)
                                <div class="mt-6">{!! $blog->body_3 !!}</div>
                            @endif
                        </div>
     @php
    $keywords = $blog->meta_keywords;

    if (is_string($keywords)) {
        $decoded = json_decode($keywords, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $keywords = $decoded;
        } else {
            $keywords = explode(',', $keywords);
        }
    }

    $keywords = is_array($keywords) ? $keywords : [];
@endphp

<div class="flex flex-wrap gap-2 mb-6">
    @foreach ($keywords as $tag)
        @php
            $tag = trim($tag);
        @endphp

        @if ($tag)
            <a href="{{ url('/blogs?tag=' . urlencode($tag)) }}"
                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-200">

                <svg xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="lucide lucide-tag h-3 w-3 mr-1">

                    <path
                        d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                    </path>

                    <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                </svg>

                {{ $tag }}
            </a>
        @endif
    @endforeach
</div>
                     
                        {{-- <!-- Comments Section -->
                        <div class=\"mt-12\">
                            <h3 class=\"text-xl font-bold text-gray-900 mb-8\">Comments (2)</h3>

                            <!-- Comments Form -->
                            <div class="rounded-lg border border-gray-200 bg-white p-6 md:p-8 mb-8 shadow-xs">
                                <h4 class="font-bold text-gray-800 mb-6">Leave a comment</h4>
                                <form class="space-y-5">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <input
                                            class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all"
                                            placeholder="Your name">
                                        <input
                                            class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all"
                                            placeholder="Email address" type="email">
                                    </div>
                                    <textarea
                                        class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all"
                                        placeholder="Write your comment..." rows="4"></textarea>
                                    <button
                                        class="bg-[#1D2128] text-primary px-4 py-2 rounded-lg shadow-xs font-xs hover:bg-black transition-all"
                                        type="submit">Post comment</button>
                                </form>
                            </div>

                            <!-- Comments List -->
                            <div class="space-y-6">
                                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-xs">
                                    <div class="flex items-start gap-4">
                                        <div
                                            class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center font-bold text-orange-600 shrink-0">
                                            R</div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-1">
                                                <span class="font-bold text-gray-900">Rina Akter</span>
                                                <span class="text-xs text-gray-500">2024-01-16</span>
                                            </div>
                                            <p class="text-gray-700 text-sm mb-3">Very beautiful and informative post!
                                                Especially the sustainable fashion part was great.</p>
                                            <div class="flex items-center gap-4">
                                                <button class="text-xs text-gray-500 hover:text-blue-600 transition-all">
                                                    <i class="fa-regular fa-thumbs-up mr-1"></i> 12
                                                </button>
                                                <button
                                                    class="text-xs text-gray-500 hover:text-blue-600 transition-all">Reply</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-xs">
                                    <div class="flex items-start gap-4">
                                        <div
                                            class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-600 shrink-0">
                                            K</div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-1">
                                                <span class="font-bold text-gray-900">Karim Ahmed</span>
                                                <span class="text-xs text-gray-500">2024-01-17</span>
                                            </div>
                                            <p class="text-gray-700 text-sm mb-3">Would have liked more details on retro
                                                style. Overall though, the post is very good.</p>
                                            <div class="flex items-center gap-4">
                                                <button class="text-xs text-gray-500 hover:text-blue-600 transition-all">
                                                    <i class="fa-regular fa-thumbs-up mr-1"></i> 12
                                                </button>
                                                <button
                                                    class="text-xs text-gray-500 hover:text-blue-600 transition-all">Reply</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                    </div>
                </div>
            </div>

            <div class="space-y-8 lg:sticky lg:top-[180px] h-max z-10">

                @if(isset($relatedPosts) && $relatedPosts->count() > 0)
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-xs">
                    <h3 class="text-lg font-black text-gray-900 mb-6 pb-4">Related Posts</h3>
                    <div class="space-y-5">
                        @foreach ($relatedPosts as $rp)
                            <a href="{{ url($rp->slug) }}" class="flex gap-4 group">
                                <div class="h-16 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                    @if ($rp->thumbnail_url)
                                        <img src="{{ $rp->thumbnail_url }}" alt="blog image"
                                            onerror="this.src='{{ asset('images/template1/frontend/default.webp') }}'"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                    @else
                                        <img src="{{ asset('./images/template1/frontend/default.webp') }}" alt="blog image"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform"
                                            loading="lazy" width="800" height="800">
                                    @endif
                                </div>
                                <div>
                                    <h4
                                        class="text-sm text-gray-700 line-clamp-2 group-hover:text-blue-600 transition-colors leading-tight mb-1">
                                        {{ $rp->title }}
                                    </h4>
                                    <p class="text-xs text-gray-500">{{ $rp->created_at->format('Y-m-d') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="rounded-lg border border-gray-200 p-8 shadow-xs text-gray-600">
                    <h3 class="text-lg font-black mb-4">Subscribe to Newsletter</h3>
                    <p class="text-gray-600 text-sm mb-6 leading-relaxed">Subscribe to our newsletter to get the latest
                        fashion trends and tips.</p>
                    <div class="space-y-3">
                        <input class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none"
                            placeholder="Your email address">
                        <button
                            class="w-full rounded-lg primary-bg px-4 py-3 text-sm font-medium text-primary hover:bg-gray-700 transition-all">
                            Subscribe
                        </button>
                    </div>
                </div>
 

            </div>
        </div>
    </section>
@endsection
@push('scripts')
    @include('components.meta-info.pixel-events', [
        'event' => 'ViewBlog',
        'data' => [
            'blog' => $blog
        ]
    ])
@endpush