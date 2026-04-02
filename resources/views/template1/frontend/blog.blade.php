@extends('template1.layouts.front')

@section('content')
    <section class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 py-16 md:py-24">
        <div class="container mx-auto px-4 text-center text-white">
            <h1 class="text-4xl md:text-5xl font-black mb-4 tracking-tight">আমাদের ব্লগ</h1>
            <p class="text-lg md:text-xl mb-8 opacity-90">ফ্যাশন, স্টাইল এবং শপিংয়ের সর্বশেষ তথ্য ও টিপস</p>

            <div class="max-w-2xl mx-auto relative">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" placeholder="ব্লগ খুঁজুন..."
                    class="w-full pl-12 pr-6 py-2 rounded-lg text-gray-800 bg-white shadow-2xl outline-none focus:ring-2 focus:ring-white/50 transition-all">
            </div>
        </div>
    </section>

    <!-- 2. Category Tabs -->
    <section class="container mx-auto py-6">
        <div class="flex flex-wrap justify-center gap-3">
            <button class="px-6 py-2 rounded-md bg-[#1D2128] text-white text-sm">সব</button>
            <button
                class="px-6 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm hover:border-gray-400 transition-all">ফ্যাশন</button>
            <button
                class="px-6 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm hover:border-gray-400 transition-all">শপিং
                গাইড</button>
            <button
                class="px-6 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm hover:border-gray-400 transition-all">যত্ন
                ও রক্ষণাবেক্ষণ</button>
            <button
                class="px-6 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm hover:border-gray-400 transition-all">বিশেষ
                অনুষ্ঠান</button>
            <button
                class="px-6 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm hover:border-gray-400 transition-all">স্টাইল
                টিপস</button>
        </div>
    </section>

    <!-- 3. Blog Grid -->
    <section class="container mx-auto py-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            <!-- Blog Card Template (Repeat this for other cards) -->
            <div
                class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <!-- Thumbnail Placeholder -->
                <div class="relative bg-gray-100 h-60 flex items-center justify-center overflow-hidden">
                    <!-- Circular Icon Pattern background like image -->
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <div class="w-64 h-64 border border-gray-400 rounded-full flex items-center justify-center">
                            <div class="w-48 h-48 border border-gray-400 rounded-full flex items-center justify-center">
                                <div class="w-32 h-32 border border-gray-400 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    <i class="fa-regular fa-image text-gray-300 text-4xl relative z-10"></i>
                    <!-- Badge -->
                    <span
                        class="absolute top-4 left-4 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">ফ্যাশন</span>
                </div>

                <!-- Card Content -->
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3 leading-snug">
                        ফ্যাশন ট্রেন্ড ২০২৪: এই বছরের সেরা স্টাইল
                    </h3>
                    <p class="text-gray-500 text-sm mb-5 line-clamp-2">
                        এই বছরের সবচেয়ে জনপ্রিয় ফ্যাশন ট্রেন্ড এবং স্টাইল টিপস যা আপনাকে আকর্ষণীয় করে তুলবে।
                    </p>

                    <!-- Meta Info -->
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1"><i class="fa-regular fa-user text-xs"></i> সারা খান</span>
                            <span class="flex items-center gap-1"><i class="fa-regular fa-calendar text-xs"></i>
                                ২০২৪-০১-১৫</span>
                        </div>
                        <span class="text-blue-600">৫ মিনিট</span>
                    </div>

                    <!-- Tags -->
                    <div class="flex flex-wrap gap-2 mb-6 font-semibold text-gray-700">
                        <span class="bg-gray-100  text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg> ট্রেন্ড
                        </span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg> স্টাইল
                        </span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg> ফ্যাশন
                        </span>
                    </div>

                    <!-- Button -->
                    <a href="#"
                        class="block w-full text-center bg-[#1D2128] hover:bg-black text-white font-bold py-3 rounded-md transition-colors text-sm">
                        বিস্তারিত পড়ুন <i class="fas fa-arrow-right ml-2 text-[11px]"></i>
                    </a>
                </div>
            </div>

            <!-- Blog Card 2 -->
            <div
                class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="relative bg-gray-100 h-60 flex items-center justify-center">
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <div class="w-64 h-64 border border-gray-400 rounded-full flex items-center justify-center"></div>
                    </div>
                    <i class="fa-regular fa-image text-gray-300 text-4xl relative z-10"></i>
                    <span class="absolute top-4 left-4 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">শপিং
                        গাইড</span>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3 leading-snug">অনলাইন শপিংয়ের সেরা টিপস</h3>
                    <p class="text-gray-500 text-sm mb-5 line-clamp-2">নিরাপদ এবং স্মার্ট অনলাইন শপিংয়ের জন্য প্রয়োজনীয় টিপস
                        এবং কৌশল।</p>
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1"><i class="fa-regular fa-user"></i> রহিম উদ্দিন</span>
                            <span class="flex items-center gap-1"><i class="fa-regular fa-calendar"></i> ২০২৪-০১-১০</span>
                        </div>
                        <span class="text-blue-600">৭ মিনিট</span>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6 font-semibold text-gray-700">
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg> অনলাইন শপিং</span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg> টিপস</span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            নিরাপত্তা</span>

                    </div>
                    <a href="#"
                        class="block w-full text-center bg-[#1D2128] hover:bg-black text-white font-bold py-3 rounded-md transition-colors text-sm">বিস্তারিত
                        পড়ুন <i class="fas fa-arrow-right ml-2 text-[11px]"></i></a>
                </div>
            </div>

            <!-- Blog Card 3 -->
            <div
                class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="relative bg-gray-100 h-60 flex items-center justify-center">
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <div class="w-64 h-64 border border-gray-400 rounded-full flex items-center justify-center"></div>
                    </div>
                    <i class="fa-regular fa-image text-gray-300 text-4xl relative z-10"></i>
                    <span
                        class="absolute top-4 left-4 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">যত্ন
                        ও রক্ষণাবেক্ষণ</span>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3 leading-snug">গ্রীষ্মকালীন পোশাকের যত্ন</h3>
                    <p class="text-gray-500 text-sm mb-5 line-clamp-2">গরমের দিনে পোশাকের যত্ন নেওয়ার সহজ উপায় এবং কার্যকর
                        পদ্ধতি।</p>
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1"><i class="fa-regular fa-user"></i> ফাতেমা আক্তার</span>
                            <span class="flex items-center gap-1"><i class="fa-regular fa-calendar"></i> ২০২৪-০১-০৫</span>
                        </div>
                        <span class="text-blue-600">৪ মিনিট</span>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6 text-gray-700 font-semibold">
                        <span class="bg-gray-100  text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            পোশাক</span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            যত্ন</span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            গ্রীষ্মকাল</span>
                    </div>
                    <a href="#"
                        class="block w-full text-center bg-[#1D2128] hover:bg-black text-white font-bold py-3 rounded-md transition-colors text-sm">বিস্তারিত
                        পড়ুন <i class="fas fa-arrow-right ml-2 text-[11px]"></i></a>
                </div>
            </div>
            <!-- Blog Card 4 -->
            <div
                class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="relative bg-gray-100 h-60 flex items-center justify-center">
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <div class="w-64 h-64 border border-gray-400 rounded-full flex items-center justify-center"></div>
                    </div>
                    <i class="fa-regular fa-image text-gray-300 text-4xl relative z-10"></i>
                    <span
                        class="absolute top-4 left-4 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">বিশেষ
                        অনুষ্ঠান</span>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3 leading-snug">বিয়ের পোশাক নির্বাচনের গাইড</h3>
                    <p class="text-gray-500 text-sm mb-5 line-clamp-2">বিয়ের জন্য নিখুঁত পোশাক নির্বাচনের সম্পূর্ণ গাইড
                        এবং পরামর্শ।</p>
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1"><i class="fa-regular fa-user"></i> নাসির আহমেদ</span>
                            <span class="flex items-center gap-1"><i class="fa-regular fa-calendar"></i> ২০২৩-১২-২৮</span>
                        </div>
                        <span class="text-blue-600">৮ মিনিট</span>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6 text-gray-700 font-semibold">
                        <span class="bg-gray-100  text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            বিয়ে</span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            পোশাক</span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            গাইড</span>
                    </div>
                    <a href="#"
                        class="block w-full text-center bg-[#1D2128] hover:bg-black text-white font-bold py-3 rounded-md transition-colors text-sm">বিস্তারিত
                        পড়ুন <i class="fas fa-arrow-right ml-2 text-[11px]"></i></a>
                </div>
            </div>
            <!-- Blog Card 5 -->
            <div
                class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="relative bg-gray-100 h-60 flex items-center justify-center">
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <div class="w-64 h-64 border border-gray-400 rounded-full flex items-center justify-center"></div>
                    </div>
                    <i class="fa-regular fa-image text-gray-300 text-4xl relative z-10"></i>
                    <span
                        class="absolute top-4 left-4 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">ফ্যাশন</span>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3 leading-snug">শীতকালীন ফ্যাশন এসেনশিয়ালস</h3>
                    <p class="text-gray-500 text-sm mb-5 line-clamp-2">শীতের জন্য অপরিহার্য পোশাক এবং এক্সেসরিজ যা আপনার
                        ওয়ারড্রোবে থাকা উচিত।</p>
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1"><i class="fa-regular fa-user"></i> তানিয়া রহমান</span>
                            <span class="flex items-center gap-1"><i class="fa-regular fa-calendar"></i> ২০২৩-১২-২০</span>
                        </div>
                        <span class="text-blue-600">৬ মিনিট</span>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6 text-gray-700 font-semibold">
                        <span class="bg-gray-100  text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            শীত</span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            ফ্যাশন</span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            এসেনশিয়ালস</span>
                    </div>
                    <a href="#"
                        class="block w-full text-center bg-[#1D2128] hover:bg-black text-white font-bold py-3 rounded-md transition-colors text-sm">বিস্তারিত
                        পড়ুন <i class="fas fa-arrow-right ml-2 text-[11px]"></i></a>
                </div>
            </div>
            <!-- Blog Card 6 -->
            <div
                class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="relative bg-gray-100 h-60 flex items-center justify-center">
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <div class="w-64 h-64 border border-gray-400 rounded-full flex items-center justify-center"></div>
                    </div>
                    <i class="fa-regular fa-image text-gray-300 text-4xl relative z-10"></i>
                    <span
                        class="absolute top-4 left-4 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">স্টাইল
                        টিপস</span>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-3 leading-snug">কালার কম্বিনেশনের শিল্প</h3>
                    <p class="text-gray-500 text-sm mb-5 line-clamp-2">পোশাকে রঙের সমন্বয় করার শিল্প এবং আকর্ষণীয় লুক
                        তৈরির কৌশল।</p>
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1"><i class="fa-regular fa-user"></i> আলিয়া খান</span>
                            <span class="flex items-center gap-1"><i class="fa-regular fa-calendar"></i> ২০২৩-১২-১৫</span>
                        </div>
                        <span class="text-blue-600">৫ মিনিট</span>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6 text-gray-700 font-semibold">
                        <span class="bg-gray-100  text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            রঙ</span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            কম্বিনেশন</span>
                        <span class="bg-gray-100 text-xs px-2 py-1 rounded-full flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tag h-3 w-3 mr-1">
                                <path
                                    d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z">
                                </path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            স্টাইল</span>
                    </div>
                    <a href="#"
                        class="block w-full text-center bg-[#1D2128] hover:bg-black text-white font-bold py-3 rounded-md transition-colors text-sm">বিস্তারিত
                        পড়ুন <i class="fas fa-arrow-right ml-2 text-[11px]"></i></a>
                </div>
            </div>

        </div>

        <!-- Load More Button -->
        <div class="mt-16 text-center">
            <button
                class="px-10 py-3 bg-white border border-gray-200 text-gray-800 font-bold rounded-md shadow-xs hover:bg-gray-50 transition-all text-sm">
                আরো ব্লগ লোড করুন
            </button>
        </div>
    </section>
@endsection
