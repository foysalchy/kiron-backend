@extends('saas.layouts.layout')
@section('meta')
    @include('components.meta-info.saas-meta', [
        'setup' => $setup,
    
        'type' => 'BlogPosting',
    
        'title' => $blogPost->meta_title ?: $blogPost->title,
    
        'description' => $blogPost->meta_description ?: Str::limit(strip_tags($blogPost->short), 160),
    
        'keywords' => is_array($blogPost->meta_keywords)
            ? implode(',', $blogPost->meta_keywords)
            : $blogPost->meta_keywords,
    
        'image' => count($blogPost->images)
            ? asset('storage/' . $blogPost->images[0])
            : asset('storage/' . $setup->logo),
    
        'canonical' => route('saas.blog.details', $blogPost->slug),
    
        'breadcrumb' => [
            [
                'name' => 'Home',
                'url' => url('/'),
            ],
            [
                'name' => 'Blog',
                'url' => route('saas.blog.list'),
            ],
            [
                'name' => $blogPost->title,
                'url' => route('saas.blog.details', $blogPost->slug),
            ],
        ],
    
        'schema' => [
            'headline' => $blogPost->title,
    
            'author' => $setup->founder_name,
    
            'published' => $blogPost->created_at,
    
            'updated' => $blogPost->updated_at,
        ],
    ])
@endsection
@push('styles')
    <style>
        table {
            width: 100%;
        }

        table th,
        table td {
            border: 1px solid black !important;
            padding: 3px 5px;
        }

        ul {
            list-style: disc;
            padding-left: 20px;
        }

        ol {
            list-style: decimal;
            padding-left: 20px;
        }

        .feature-content h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .feature-content h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 14px;
            border-left: 5px solid #34a487;
            padding-left: 15px;
        }

        .feature-content h3 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .feature-content p {
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 16px;
            text-align: justify;
            color: #333333fa;
        }

        .feature-content img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin: 16px 0;
        }

        .feature-content a {
            color: #34a487;
            text-decoration: underline;
            font-weight: 500;
        }

        .hr {
            height: 1px;
            background: gainsboro;
            margin-top: 30px;
        }
    </style>
@endpush

@section('content')

    <!-- ১. Breadcrumb Section (Same design as your blog) -->
    <section class="">
        <div class="container mx-auto px-6 md:px-10">
            <div class="flex gap-2 text-sm  px-4 py-3 ">
                <a href="{{ route('saas.index') }}" class="text-gray-600 hover:text-black transition">Home</a>
                <span class="text-gray-300"></span>
                <a href="{{ route('saas.blog.list') }}" class="text-gray-600 hover:text-black transition">Blog</a>
                <span class="text-gray-300">›</span>
                <span class="text-black font-semibold">{{ $blogPost->title }}</span>
            </div>
        </div>
    </section>

    <!-- ২. Main Content Section -->
    <section class="bg-white pb-20" style="padding-top: 40px;">
        <div class="container mx-auto px-6 md:px-10">

            <div class="grid grid-cols-12 gap-8 lg:gap-16">

                <!-- Left Sidebar: Social (Sticky) -->
                <div class="hidden lg:flex lg:col-span-1 justify-center">
                    <div class="sticky top-52 flex flex-col gap-6 h-fit pt-2">
                        @if (isset($socialLinks))
                            @foreach ($socialLinks as $social)
                                <a href="{{ $social->link ?? $social->url }}" target="_blank"
                                    class="w-10 h-10 rounded-full bg-[#34a487] flex items-center justify-center hover:bg-black hover:text-[#34a487] transition-all shadow-sm text-white">

                                    @if ($social->short)
                                        {!! $social->short !!}
                                    @else
                                        <i
                                            class="{{ !empty($social->icon_class) ? $social->icon_class : 'fab fa-facebook-f' }}"></i>
                                    @endif
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Middle: Feature Content (Col 7) -->
                <div class="col-span-12 lg:col-span-7">
                    <div class="flex flex-wrap justify-between items-center pb-6 gap-4">
                        <span
                            class="bg-[#1A1A1A] text-white px-5 py-1.5 text-xs font-bold uppercase tracking-[0.2em] rounded-sm">
                            {{ $blogPost->company->shop_name ?? 'Admin' }}
                        </span>
                        <div class="text-gray-500 text-xs font-bold uppercase tracking-widest flex items-center">
                            Last Updated: {{ $blogPost->updated_at->format('F d, Y') }}
                        </div>
                    </div>


                    <img src="{{ $blogPost->thumbnail_url ? asset($blogPost->thumbnail_url) : asset('images/saas/live1.png') }}"
                        class="w-full  im mb-8" alt="{{ $blogPost->title }}" />


                    <h1 class="text-3xl md:text-[36px] font-black text-gray-900 leading-[1.1] mb-6">
                        {{ $blogPost->title }}
                    </h1>

                    <div class="feature-content">
                        {{-- Subtitle / Short Description --}}
                        <div
                            class="text-lg text-gray-600 font-medium mb-8 leading-relaxed italic border-l-4 border-gray-200 pl-5">
                            {{ $blogPost->short ?? 'No short description available.' }}
                        </div>

                        {{-- Main Description (Rich Text) --}}
                        <div class="prose prose-slate max-w-none">
                            {!! $blogPost->body !!}
                        </div>
                        <div class="prose prose-slate max-w-none">
                            {!! $blogPost->body_2 !!}
                        </div>
                        <div class="prose prose-slate max-w-none">
                            {!! $blogPost->body_3 !!}
                        </div>
                    </div>


                </div>

                <!-- Right Sidebar: Related Features (Sticky) -->
                <div class="col-span-12 lg:col-span-4">
                    <div class="sticky top-52 lg:pl-4 h-fit">
                        <h3
                            class="text-sm font-bold text-gray-900 mb-8 uppercase tracking-[0.2em] border-l-4 border-[#34a487] pl-4">
                            More Blog Posts
                        </h3>
                        <div class="flex flex-col gap-10">
                            @foreach ($otherBlogPosts as $other)
                                <a href="{{ route('saas.blog.details', $other->slug ?? $other->id) }}"
                                    class="flex items-start gap-4 group" style="text-decoration: none;">
                                    <div
                                        class="w-24 h-24 flex-shrink-0 overflow-hidden bg-gray-100 rounded-lg shadow-sm border border-gray-50">
                                        <img src="{{ $other->image_url ?? asset('images/saas/live1.png') }}"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            alt="{{ $other->title }}" />
                                    </div>
                                    <div class="flex flex-col pt-1">
                                        <h4
                                            class="text-gray-900 font-bold leading-snug text-sm transition-colors group-hover:text-[#34a487]">
                                            {{ Str::limit($other->title, 50) }}
                                        </h4>
                                        <span
                                            class="text-[10px] text-gray-400 font-bold uppercase mt-2 tracking-widest">Read
                                            More ›</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
