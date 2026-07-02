@extends('saas.layouts.layout')

@section('content')
    <!-- HEADER SECTION -->
    <section class="bg-[#34a487] pt-32 pb-20 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#34a487]/10 blur-[120px] rounded-full"></div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <h1 class="text-white text-4xl md:text-6xl font-black mb-6">Our Blogs and Articles</h1>
            <p class="text-gray-800 text-lg md:text-xl max-w-2xl mx-auto">
                Stay updated with the latest trends, tips, and guides on business growth, modern technology, and e-commerce solutions.
            </p>
        </div>
    </section>

    <!-- FEATURES GRID SECTION -->
    <section class="bg-white py-10">
        <div class="container mx-auto px-6 md:px-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                 @foreach ($blogPosts as $blog)
                        <div
                            class="bg-white border border-gray-100 rounded-2xl overflow-hidden group hover:shadow-xl transition-all duration-300 flex flex-col h-full">

                            <a href="{{ route('saas.blog.details', $blog->slug) }}" class="group block overflow-hidden rounded-xl">
                                <div class="aspect-[16/10] bg-[#eef2ff] relative overflow-hidden">
                                    <img src="{{ $blog->thumbnail_url ? asset($blog->thumbnail_url) : asset('images/saas/live1.png') }}"
                                        alt="{{ $blog->title }}"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                </div>
                            </a>

                            <div class="p-6 md:p-8 flex flex-col flex-grow">
                                <div class="flex justify-between items-center mb-5">
                                    <span
                                        class="bg-indigo-50 text-[#34a487] px-4 py-1 rounded-full text-xs font-bold border border-indigo-100">
                                        {{ $blog->company->shop_name ?? 'Admin' }}
                                    </span>
                                    <div class="flex items-center gap-2 text-gray-400 text-sm font-bold">
                                        <i class="fa-regular fa-clock"></i>
                                        <span>{{ $blog->reading_time ?? '' }} minutes</span>
                                    </div>
                                </div>

                                <h3
                                    class="text-xl md:text-2xl font-semibold text-gray-900 mb-4 leading-tight line-clamp-2">
                                    {{ $blog->title ?? '' }}
                                </h3>

                                <p class="text-gray-500 text-sm mb-8 line-clamp-3">
                                    {{ $blog->short ?? '' }}
                                </p>

                                <div class="mt-auto pt-5 border-t border-gray-50">
                                    <a href="{{ route('saas.blog.details', $blog->slug) }}"
                                        class="inline-flex items-center gap-2 text-[#34a487] font-bold text-lg group-hover:gap-3 transition-all">
                                        Read More <i class="fa-solid fa-arrow-right text-sm"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
            </div>

            <div class="mt-16">
                {{ $blogPosts->links() }}
            </div>
        </div>
    </section>
@endsection
