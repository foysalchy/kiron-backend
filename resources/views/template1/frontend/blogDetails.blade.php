@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto py-6">

        <!-- ব্যাক বাটন: ডাইনামিক ইউআরএল -->
        <a class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-6 font-medium" href="{{ url('/blogs') }}">
            <i class="fas fa-arrow-left mr-2 text-sm"></i> ব্লগ তালিকায় ফিরে যান
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- বাম: মেইন কন্টেন্ট -->
            <div class="lg:col-span-2">
                <div class="rounded-lg border border-gray-200 bg-white shadow-xs overflow-hidden">

                    <!-- ফিচার ইমেজ: ডাইনামিক -->
                    <div class="relative">
                        @if(!empty($blog->images) && isset($blog->images[0]))
                            <img src="{{ asset('storage/' . $blog->images[0]) }}" alt="{{ $blog->title }}"
                                class="w-full h-64 md:h-[450px] object-cover">
                        @else
                            <div class="w-full h-64 md:h-[450px] bg-gray-100 flex items-center justify-center text-gray-300">
                                <i class="fa-regular fa-image text-6xl"></i>
                            </div>
                        @endif

                        @php
                            $keywords = is_array($blog->meta_keywords) ? $blog->meta_keywords : explode(',', $blog->meta_keywords);
                        @endphp
                        <div class="absolute top-4 left-4 bg-blue-600 text-white px-4 py-1 rounded-full text-xs font-bold shadow-lg">
                            {{ $keywords[0] ?? 'ব্লগ' }}
                        </div>
                    </div>

                    <div class="p-6 md:p-10">

                        <!-- Title: ডাইনামিক -->
                        <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-6 leading-tight">
                            {{ $blog->title }}
                        </h1>

                        <!-- Meta: ডাইনামিক -->
                        <div class="flex flex-wrap items-center gap-6 text-sm text-gray-500 mb-8 pb-6">
                            <div class="flex items-center gap-2">
                                <i class="fa-regular fa-user text-blue-500"></i>
                                <span>{{ $blog->user->name ?? 'অ্যাডমিন' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fa-regular fa-calendar text-blue-500"></i>
                                <span>{{ $blog->created_at->format('Y-m-d') }}</span>
                            </div>
                            <span class="text-blue-600">
                                {{ $blog->reading_time ?? 5 }} মিনিট
                            </span>
                        </div>

                        <!-- Tags: ডাইনামিক -->
                        <div class="flex flex-wrap gap-2 mb-8">
                            @foreach($keywords as $tag)
                                @if(trim($tag))
                                <a href="{{ url('/blogs?tag=' . urlencode(trim($tag))) }}"
                                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle></svg> {{ trim($tag) }}
                                </a>
                                @endif
                            @endforeach
                        </div>

                       <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed space-y-6 mb-12">
                            {!! nl2br($blog->body) !!}

                            @if($blog->body_2)
                                <div class="mt-6">{!! nl2br($blog->body_2) !!}</div>
                            @endif

                            @if($blog->body_3)
                                <div class="mt-6">{!! nl2br($blog->body_3) !!}</div>
                            @endif
                        </div>

                        <div class="h-[1px] w-full bg-gray-200 my-10"></div>

                        <div class="flex items-center gap-5 p-6 bg-blue-50/50 rounded-lg mb-12">
                            <div class="h-16 w-16 rounded-full bg-gray-200 overflow-hidden flex-shrink-0 shadow-sm border-2 border-white">
                                <img src="{{ ($blog->user && $blog->user->profile) ? asset('storage/' . $blog->user->profile) : asset('images/template1/frontend/default.webp') }}"
                                    alt="{{ $blog->user->name ?? 'Author' }}"
                                    class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">{{ $blog->user->name ?? 'অ্যাডমিন' }}</h3>
                                {{-- <p class="text-gray-600 text-sm">পেশাদার কন্টেন্ট রাইটার ও ফ্যাশন বিশেষজ্ঞ। তিনি নিয়মিত আমাদের সাথে কাজ করছেন।</p> --}}
                            </div>
                        </div>
                        {{-- <!-- মন্তব্য সেকশন -->
                        <div class="mt-12">
                            <h3 class="text-xl font-bold text-gray-900 mb-8">মন্তব্য (2)</h3>

                            <!-- মন্তব্য ফরম -->
                            <div class="rounded-lg border border-gray-200 bg-white p-6 md:p-8 mb-8 shadow-xs">
                                <h4 class="font-bold text-gray-800 mb-6">একটি মন্তব্য করুন</h4>
                                <form class="space-y-5">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <input
                                            class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all"
                                            placeholder="আপনার নাম">
                                        <input
                                            class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all"
                                            placeholder="ইমেইল ঠিকানা" type="email">
                                    </div>
                                    <textarea
                                        class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all"
                                        placeholder="আপনার মন্তব্য লিখুন..." rows="4"></textarea>
                                    <button
                                        class="bg-[#1D2128] text-white px-4 py-2 rounded-lg shadow-xs font-xs hover:bg-black transition-all"
                                        type="submit">মন্তব্য পোস্ট করুন</button>
                                </form>
                            </div>

                            <!-- মন্তব্যের তালিকা -->
                            <div class="space-y-6">
                                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-xs">
                                    <div class="flex items-start gap-4">
                                        <div
                                            class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center font-bold text-orange-600 shrink-0">
                                            র</div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-1">
                                                <span class="font-bold text-gray-900">রিনা আক্তার</span>
                                                <span class="text-xs text-gray-500">২০২৪-০১-১৬</span>
                                            </div>
                                            <p class="text-gray-700 text-sm mb-3">খুবই সুন্দর এবং তথ্যবহুল পোস্ট! বিশেষ করে
                                                সাস্টেইনেবল ফ্যাশনের অংশটি খুব ভালো লেগেছে।</p>
                                            <div class="flex items-center gap-4">
                                                <button class="text-xs text-gray-500 hover:text-blue-600 transition-all">
                                                    <i class="fa-regular fa-thumbs-up mr-1"></i> 12
                                                </button>
                                                <button
                                                    class="text-xs text-gray-500 hover:text-blue-600 transition-all">উত্তর
                                                    দিন</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-xs">
                                    <div class="flex items-start gap-4">
                                        <div
                                            class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-600 shrink-0">
                                            ক</div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-1">
                                                <span class="font-bold text-gray-900">করিম সাহেব</span>
                                                <span class="text-xs text-gray-500">২০২৪-০১-১৭</span>
                                            </div>
                                            <p class="text-gray-700 text-sm mb-3">রেট্রো স্টাইল নিয়ে আরও বিস্তারিত লিখলে
                                                ভালো হতো। তবে সামগ্রিকভাবে পোস্টটি অনেক ভালো।</p>
                                            <div class="flex items-center gap-4">
                                                <button class="text-xs text-gray-500 hover:text-blue-600 transition-all">
                                                    <i class="fa-regular fa-thumbs-up mr-1"></i> 12
                                                </button>
                                                <button
                                                    class="text-xs text-gray-500 hover:text-blue-600 transition-all">উত্তর
                                                    দিন</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                    </div>
                </div>
            </div>

            <!-- ডান: সাইডবার -->
            <div class="space-y-8">

                <!-- সম্পর্কিত পোস্ট: ডাইনামিক -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-xs">
                    <h3 class="text-lg font-black text-gray-900 mb-6 pb-4">সম্পর্কিত পোস্ট</h3>
                    <div class="space-y-5">
                       @foreach($relatedPosts as $rp)
                        <a href="{{ route('blog.details', ['store' => request()->route('store'), 'slug' => $rp->slug]) }}" class="flex gap-4 group">
                            <div class="h-16 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                @if(!empty($rp->images) && isset($rp->images[0]))
                                    <img src="{{ asset('storage/' . $rp->images[0]) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300"><i class="fa-regular fa-image"></i></div>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-sm text-gray-700 line-clamp-2 group-hover:text-blue-600 transition-colors leading-tight mb-1">
                                    {{ $rp->title }}
                                </h4>
                                <p class="text-xs text-gray-500">{{ $rp->created_at->format('Y-m-d') }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- নিউজলেটার: ডিজাইন ঠিক রাখা হয়েছে -->
                <div class="rounded-lg border border-gray-200 p-8 shadow-xs text-gray-600">
                    <h3 class="text-lg font-black mb-4">নিউজলেটার সাবস্ক্রাইব করুন</h3>
                    <p class="text-gray-600 text-sm mb-6 leading-relaxed">সর্বশেষ ফ্যাশন ট্রেন্ড এবং টিপস পেতে আমাদের নিউজলেটার সাবস্ক্রাইব করুন।</p>
                    <div class="space-y-3">
                        <input class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm focus:outline-none" placeholder="আপনার ইমেইল ঠিকানা">
                        <button class="w-full rounded-lg bg-gray-900 px-4 py-3 text-sm font-medium text-white hover:bg-gray-700 transition-all">
                            সাবস্ক্রাইব করুন
                        </button>
                    </div>
                </div>

                <!-- জনপ্রিয় ট্যাগ: ডাইনামিক -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-xs">
                    <h3 class="text-lg font-black text-gray-900 mb-6 pb-4">জনপ্রিয় ট্যাগ</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($popularTags as $tag)
                            <a href="{{ url('/blogs?tag=' . urlencode($tag)) }}"
                                class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-bold text-gray-900 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all">
                                {{ $tag }}
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
