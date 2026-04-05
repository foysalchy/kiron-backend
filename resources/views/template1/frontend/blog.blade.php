@extends('template1.layouts.front')

@section('content')
    <!-- 1. Hero & Search -->
    <section class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 py-16 md:py-24">
        <div class="container mx-auto px-4 text-center text-white">
            <h1 class="text-4xl md:text-5xl font-black mb-4 tracking-tight">আমাদের ব্লগ</h1>
            <p class="text-lg md:text-xl mb-8 opacity-90">ফ্যাশন, স্টাইল এবং শপিংয়ের সর্বশেষ তথ্য ও টিপস</p>

            <form action="{{ url()->current() }}" method="GET" class="max-w-2xl mx-auto relative">
                @if(request('tag')) <input type="hidden" name="tag" value="{{ request('tag') }}"> @endif
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ব্লগ খুঁজুন..."
                    class="w-full pl-12 pr-6 py-4 rounded-xl text-gray-800 bg-white shadow-2xl outline-none focus:ring-4 focus:ring-white/20 transition-all">
            </form>
        </div>
    </section>

    <!-- 2. Category Tabs -->
    <section class="container mx-auto py-8 px-4">
        <div class="flex flex-wrap justify-center gap-3">
            @php
                $currentTag = request('tag');
                $tags = ['ফ্যাশন', 'শপিং গাইড', 'যত্ন ও রক্ষণাবেক্ষণ', 'বিশেষ অনুষ্ঠান', 'স্টাইল টিপস'];
            @endphp

            <a href="{{ url()->current() }}"
               class="px-6 py-2 rounded-md text-sm font-bold transition-all {{ empty($currentTag) ? 'bg-[#1D2128] text-white shadow-lg' : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-400' }}">
               সব
            </a>

            @foreach($tags as $tag)
                <a href="{{ url()->current() . '?tag=' . $tag }}"
                   class="px-6 py-2 rounded-md text-sm font-bold transition-all {{ $currentTag == $tag ? 'bg-[#1D2128] text-white shadow-lg' : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-400' }}">
                   {{ $tag }}
                </a>
            @endforeach
        </div>
    </section>

    <!-- 3. Blog Grid -->
    <section class="container mx-auto py-6 px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            @forelse($blogs as $blog)
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group">

                    <!-- Image / Placeholder Area (Matching 2nd Image) -->
                    <div class="relative h-64 bg-gray-50 flex items-center justify-center overflow-hidden">
                        @if($blog->thumbnail_url)
                            <img src="{{ $blog->thumbnail_url }}" alt="{{ $blog->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <!-- The Circular Pattern Background from your 2nd image -->
                            <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                                <div class="w-64 h-64 border border-gray-300 rounded-full flex items-center justify-center">
                                    <div class="w-48 h-48 border border-gray-300 rounded-full flex items-center justify-center">
                                        <div class="w-32 h-32 border border-gray-300 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                            <i class="fa-regular fa-image text-gray-300 text-5xl relative z-10"></i>
                        @endif

                        <!-- Category Badge -->
                        @php
                            $badgeText = is_array($blog->meta_keywords) ? ($blog->meta_keywords[0] ?? 'ব্লগ') : ($blog->meta_keywords ?? 'ব্লগ');
                        @endphp
                        <span class="absolute top-4 left-4 bg-blue-600 text-white text-sm font-bold px-3 py-1 rounded-full shadow-md z-20">
                            {{ $badgeText }}
                        </span>
                    </div>

                    <!-- Card Content -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-3 leading-tight line-clamp-2 group-hover:text-blue-600 transition-colors">
                            {{ $blog->title }}
                        </h3>
                        <p class="text-gray-700 text-sm mb-6 line-clamp-2">
                            {{ $blog->short ?? \Illuminate\Support\Str::limit(strip_tags($blog->body), 100) }}
                        </p>

                        <!-- Metadata (Author, Date, Read Time) -->
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-5 font-medium border-b border-gray-50 pb-4">
                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1.5"><i class="fa-regular fa-user text-sm"></i> এডমিন</span>
                                <span class="flex items-center gap-1.5"><i class="fa-regular fa-calendar text-sm"></i> {{ $blog->created_at->format('Y-m-d') }}</span>
                            </div>
                            <span class="text-blue-600 font-medium">
                                {{ round(str_word_count(strip_tags($blog->body)) / 200) + 1 }} মিনিট
                            </span>
                        </div>

                        <!-- Tags Section with Tag Icon -->
                        <div class="flex flex-wrap gap-3 mb-6">
                            @php
                                $keywords = is_array($blog->meta_keywords) ? $blog->meta_keywords : explode(',', $blog->meta_keywords);
                            @endphp
                            @if(!empty($keywords))
                                @foreach(array_slice($keywords, 0, 3) as $keyword)
                                    @if(trim($keyword))
                                    <span class="text-sm text-gray-700 flex items-center gap-1.5 bg-gray-100 px-2 py-1 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle></svg>
                                         {{ trim($keyword) }}
                                    </span>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <!-- Read More Button -->
                        <a href="{{ url('blog/' . $blog->slug) }}"
                            class="block w-full text-center bg-[#1D2128] hover:bg-blue-600 text-white font-bold py-3 rounded-xl transition-all duration-300 text-sm">
                            বিস্তারিত পড়ুন <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-20 bg-white rounded-2xl border border-dashed">
                    <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" class="w-24 h-24 mx-auto opacity-10 mb-4">
                    <h2 class="text-xl font-bold text-gray-400">এই ক্যাটাগরিতে কোনো ব্লগ পাওয়া যায়নি।</h2>
                </div>
            @endforelse

        </div>

        <!-- Pagination (Designed to match the "Show Results" bar) -->
        <div class="mt-12 flex flex-col items-center gap-4">
            <p class="text-sm text-gray-500">Showing {{ $blogs->firstItem() }} to {{ $blogs->lastItem() }} of {{ $blogs->total() }} results</p>
            <div class="flex justify-center">
                {{ $blogs->links() }}
            </div>
        </div>
    </section>
@endsection
