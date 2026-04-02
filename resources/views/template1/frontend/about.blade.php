@extends('template1.layouts.front')

@section('content')
    <!-- ABOUT US HERO SECTION -->
    <section class="py-6 mx-auto container">
        <div class="text-center">

            <!-- Badge -->
            <div
                class="inline-flex items-center rounded-full px-5 py-1.5 text-xs font-bold transition-colors mb-6 bg-orange-50 text-[#FF6A00] border border-orange-100 uppercase tracking-widest">
                আমাদের সম্পর্কে
            </div>

            <!-- Main Heading -->
            <h1 class="text-2xl md:text-5xl font-black text-gray-900 mb-8 leading-[1.2]">
                আমরা আপনার <span class="text-[#FF6A00] relative inline-block">
                    বিশ্বস্ত
                    <svg class="absolute -bottom-2 left-0 w-full h-2 text-orange-200" viewBox="0 0 100 10"
                        preserveAspectRatio="none">
                        <path d="M0 5 Q 25 0, 50 5 T 100 5" fill="none" stroke="currentColor" stroke-width="4" />
                    </svg>
                </span>
                অনলাইন শপিং পার্টনার
            </h1>

            <!-- Description -->
            <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed font-medium">
                ২০১৯ সাল থেকে আমরা বাংলাদেশের মানুষের কাছে উন্নত মানের পণ্য পৌঁছে দিচ্ছি। আমাদের লক্ষ্য হলো প্রতিটি গ্রাহকের
                কাছে সেরা পণ্য এবং সেবা পৌঁছানো।
            </p>
        </div>
    </section>
    <section class="container py-6 mx-auto">
        <!-- STATS SECTION -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8 font-['Outfit']">

            <!-- Stat 1: Customers -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 text-center group shadow-xs">
                <div
                    class="bg-orange-50 text-[#FF6A00] p-4  rounded-full inline-flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-users">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 class="text-xl md:text-3xl font-black text-gray-900 mb-2 tracking-tight">১০,০০০+</h3>
                <p class="text-gray-600 font-medium text-sm uppercase tracking-wider">সন্তুষ্ট গ্রাহক</p>
            </div>

            <!-- Stat 2: Products -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 text-center group shadow-xs">
                <div
                    class="bg-orange-50 text-[#FF6A00] p-4 rounded-full inline-flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-award">
                        <path
                            d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526">
                        </path>
                        <circle cx="12" cy="8" r="6"></circle>
                    </svg>
                </div>
                <h3 class="text-xl md:text-3xl font-black text-gray-900 mb-2 tracking-tight">৫,০০০+</h3>
                <p class="text-gray-600 font-medium text-sm uppercase tracking-wider">পণ্যের সংখ্যা</p>
            </div>

            <!-- Stat 3: Delivery -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-xs text-center group">
                <div
                    class="bg-orange-50 text-[#FF6A00] p-4 rounded-full inline-flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-truck">
                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                        <path d="M15 18H9"></path>
                        <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path>
                        <circle cx="17" cy="18" r="2"></circle>
                        <circle cx="7" cy="18" r="2"></circle>
                    </svg>
                </div>
                <h3 class="text-xl md:text-3xl font-black text-gray-900 mb-2 tracking-tight">৯৮%</h3>
                <p class="text-gray-600 font-medium text-sm uppercase tracking-wider">সফল ডেলিভারি</p>
            </div>

            <!-- Stat 4: Experience -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-xs text-center group">
                <div
                    class="bg-orange-50 text-[#FF6A00] p-4 rounded-full inline-flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-star">
                        <path
                            d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl md:text-3xl font-black text-gray-900 mb-2 tracking-tight">৫+</h3>
                <p class="text-gray-600 font-medium text-sm uppercase tracking-wider">বছরের অভিজ্ঞতা</p>
            </div>

        </div>
    </section>
    <section class="container py-6 mx-auto">
        <!-- MISSION & VISION SECTION -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 font-['Outfit']">

            <!-- আমাদের লক্ষ্য (Goal) -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-xs relative overflow-hidden group">

                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex items-center justify-center text-orange-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-target">
                                <circle cx="12" cy="12" r="10"></circle>
                                <circle cx="12" cy="12" r="6"></circle>
                                <circle cx="12" cy="12" r="2"></circle>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900">আমাদের লক্ষ্য</h3>
                    </div>

                    <p class="text-gray-600 leading-relaxed text-md font-medium">
                        আমাদের লক্ষ্য হলো বাংলাদেশের প্রতিটি মানুষের কাছে উন্নত মানের পণ্য সাশ্রয়ী মূল্যে পৌঁছে দেওয়া।
                        আমরা চাই যেন অনলাইন শপিং হয় সহজ, নিরাপদ এবং আনন্দদায়ক। গ্রাহক সন্তুষ্টিই আমাদের প্রধান উদ্দেশ্য।
                    </p>
                </div>
            </div>

            <!-- আমাদের দৃষ্টিভঙ্গি (Vision) -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-xs relative overflow-hidden group">

                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="text-orange-500 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-eye">
                                <path
                                    d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                </path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900">আমাদের দৃষ্টিভঙ্গি</h3>
                    </div>

                    <p class="text-gray-600 leading-relaxed text-md font-medium">
                        আমরা স্বপ্ন দেখি একটি ডিজিটাল বাংলাদেশের, যেখানে প্রতিটি মানুষ ঘরে বসেই তাদের প্রয়োজনীয় সব পণ্য
                        পেতে পারবেন। আমরা হতে চাই বাংলাদেশের সবচেয়ে বিশ্বস্ত এবং জনপ্রিয় অনলাইন শপিং প্ল্যাটফর্ম।
                    </p>
                </div>
            </div>

        </div>
    </section>
    <!-- OUR VALUES SECTION -->
    <section class="container py-6 mx-auto">

        <!-- Section Header -->
        <div class="text-center mb-10">
            <h2 class="text-xl md:text-3xl font-black text-gray-900 mb-6 tracking-tight">আমাদের মূল্যবোধ</h2>
            <p class="text-md text-gray-600 max-w-2xl mx-auto leading-relaxed">
                যে মূল্যবোধগুলো আমাদের প্রতিটি কাজে অনুপ্রেরণা দেয় এবং আমাদের সেবার মান নির্ধারণ করে।
            </p>
        </div>

        <!-- Values Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

            <!-- Value 1: Customer Service -->
            <div
                class="bg-white rounded-lg border border-gray-200 p-4 text-center transition-all duration-300 group shadow-sm hover:shadow-xl">
                <div
                    class="bg-orange-50 text-[#FF6A00] p-4 rounded-full inline-flex items-center justify-center mb-8 group-hover:bg-[#FF6A00] group-hover:text-white transition-all duration-300 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-heart">
                        <path
                            d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 tracking-tight">গ্রাহক সেবা</h3>
                <p class="text-gray-600 text-sm leading-relaxed">আমাদের গ্রাহকরাই আমাদের অগ্রাধিকার। আমরা সর্বদা সেরা সেবা
                    প্রদানে প্রতিশ্রুতিবদ্ধ।</p>
            </div>

            <!-- Value 2: Trust -->
            <div
                class="bg-white rounded-lg border border-gray-200 p-4 text-center transition-all duration-300 group shadow-sm hover:shadow-xl">
                <div
                    class="bg-orange-50 text-[#FF6A00] p-4 rounded-full inline-flex items-center justify-center mb-8 group-hover:bg-[#FF6A00] group-hover:text-white transition-all duration-300 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-shield">
                        <path
                            d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 tracking-tight">বিশ্বস্ততা</h3>
                <p class="text-gray-600 text-sm leading-relaxed">আমরা শুধুমাত্র উন্নত মানের পণ্য বিক্রি করি এবং গ্রাহকদের
                    সাথে স্বচ্ছতা বজায় রাখি।</p>
            </div>

            <!-- Value 3: Delivery -->
            <div
                class="bg-white rounded-lg border border-gray-200 p-4 text-center transition-all duration-300 group shadow-sm hover:shadow-xl">
                <div
                    class="bg-orange-50 text-[#FF6A00] p-4 rounded-full inline-flex items-center justify-center mb-8 group-hover:bg-[#FF6A00] group-hover:text-white transition-all duration-300 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-truck">
                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                        <path d="M15 18H9"></path>
                        <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path>
                        <circle cx="17" cy="18" r="2"></circle>
                        <circle cx="7" cy="18" r="2"></circle>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 tracking-tight">দ্রুত ডেলিভারি</h3>
                <p class="text-gray-600 text-sm leading-relaxed">সময়মতো এবং নিরাপদ ডেলিভারি নিশ্চিত করতে আমাদের রয়েছে
                    দক্ষ ডেলিভারি টিম।</p>
            </div>

            <!-- Value 4: Quality -->
            <div
                class="bg-white rounded-lg border border-gray-200 p-4 text-center transition-all duration-300 group shadow-sm hover:shadow-xl">
                <div
                    class="bg-orange-50 text-[#FF6A00] p-4 rounded-full inline-flex items-center justify-center mb-8 group-hover:bg-[#FF6A00] group-hover:text-white transition-all duration-300 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-award">
                        <path
                            d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526">
                        </path>
                        <circle cx="12" cy="8" r="6"></circle>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 tracking-tight">গুণগত মান</h3>
                <p class="text-gray-600 text-sm leading-relaxed">প্রতিটি পণ্য কঠোর মান নিয়ন্ত্রণের মধ্য দিয়ে যায় এবং
                    সেরা মানের নিশ্চয়তা দেয়।</p>
            </div>

        </div>
    </section>
    <!-- OUR TEAM SECTION -->
    <section class="container py-6 mx-auto">

        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-xl md:text-3xl font-black text-gray-900 mb-6 tracking-tight">আমাদের টিম</h2>
            <p class="text-md text-gray-700 max-w-2xl mx-auto leading-relaxed">
                আমাদের দক্ষ এবং অভিজ্ঞ টিম সদস্যরা যারা আপনার সেবায় নিয়োজিত।
            </p>
        </div>

        <!-- Team Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

            <!-- Member 1 -->
            <div
                class="bg-white rounded-lg border border-gray-200 p-4 text-center shadow-sm hover:shadow-xl transition-all duration-300 group">
                <div class="relative w-28 h-28 mx-auto mb-6">
                    <div class="absolute inset-2 rounded-full overflow-hidden bg-gray-100">
                        <img src="https://via.placeholder.com/200x200/FF6A00/FFFFFF?text=CEO" alt="মোহাম্মদ রহিম"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">মোহাম্মদ রহিম</h3>
                <p class="text-[#FF6A00] text-sm uppercase tracking-wider mb-4">প্রতিষ্ঠাতা ও সিইও</p>
                <p class="text-gray-700 text-sm leading-relaxed">১০+ বছরের ব্যবসায়িক অভিজ্ঞতা সহ ই-কমার্স বিশেষজ্ঞ।</p>
            </div>

            <!-- Member 2 -->
            <div
                class="bg-white rounded-lg border border-gray-200 p-4 text-center shadow-sm hover:shadow-xl transition-all duration-300 group">
                <div class="relative w-28 h-28 mx-auto mb-6">
                    <div class="absolute inset-2 rounded-full overflow-hidden bg-gray-100">
                        <img src="https://via.placeholder.com/200x200/FF6A00/FFFFFF?text=PM" alt="ফাতেমা খাতুন"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">ফাতেমা খাতুন</h3>
                <p class="text-[#FF6A00] text-sm uppercase tracking-wider mb-4">পণ্য ব্যবস্থাপক</p>
                <p class="text-gray-700 text-sm leading-relaxed">পণ্যের মান নিয়ন্ত্রণ এবং সোর্সিং এর দায়িত্বে রয়েছেন।</p>
            </div>

            <!-- Member 3 -->
            <div
                class="bg-white rounded-lg border border-gray-200 p-4 text-center shadow-sm hover:shadow-xl transition-all duration-300 group">
                <div class="relative w-28 h-28 mx-auto mb-6">
                    <div class="absolute inset-2 rounded-full overflow-hidden bg-gray-100">
                        <img src="https://via.placeholder.com/200x200/FF6A00/FFFFFF?text=CS" alt="আহমেদ হাসান"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">আহমেদ হাসান</h3>
                <p class="text-[#FF6A00] text-sm uppercase tracking-wider mb-4">গ্রাহক সেবা প্রধান</p>
                <p class="text-gray-700 text-sm leading-relaxed">গ্রাহক সন্তুষ্টি নিশ্চিত করতে ২৪/৭ কাজ করেন।</p>
            </div>

            <!-- Member 4 -->
            <div
                class="bg-white rounded-lg border border-gray-200 p-4 text-center shadow-sm hover:shadow-xl transition-all duration-300 group">
                <div class="relative w-28 h-28 mx-auto mb-6">
                    <div class="absolute inset-2 rounded-full overflow-hidden bg-gray-100">
                        <img src="https://via.placeholder.com/200x200/FF6A00/FFFFFF?text=LM" alt="নাসির উদ্দিন"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">নাসির উদ্দিন</h3>
                <p class="text-[#FF6A00] text-sm uppercase tracking-wider mb-4">লজিস্টিক ম্যানেজার</p>
                <p class="text-gray-700 text-sm leading-relaxed">দেশব্যাপী দ্রুত ও নিরাপদ ডেলিভারি নিশ্চিত করেন।</p>
            </div>

        </div>
    </section>
    <!-- WHY CHOOSE US SECTION -->
    <section class="container py-6 mx-auto">

        <!-- Section Header -->
        <div class="text-center mb-16">
            <h2 class="text-xl md:text-3xl font-black text-gray-900 mb-6 tracking-tight">কেন আমাদের বেছে নেবেন?</h2>
            <p class="text-md text-gray-700 max-w-2xl mx-auto leading-relaxed">
                আমাদের সেবার বিশেষত্ব যা আমাদের অন্যদের থেকে আলাদা করে তোলে।
            </p>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- Feature 1: Original Products -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 border-l-4 border-l-green-500 group shadow-xs">
                <h3 class="text-xl font-bold text-green-600 mb-4 tracking-tight">১০০% অরিজিনাল পণ্য</h3>
                <p class="text-gray-700 leading-relaxed font-medium">
                    আমরা শুধুমাত্র অরিজিনাল এবং উন্নত মানের পণ্য বিক্রি করি। প্রতিটি পণ্যের জন্য আমাদের রয়েছে গ্যারান্টি
                    এবং স্বচ্ছতা।
                </p>
            </div>

            <!-- Feature 2: Fast Delivery -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 border-l-4 border-l-[#FF6A00] group shadow-xs">
                <h3 class="text-xl font-bold text-[#FF6A00] mb-4 tracking-tight text-[#FF6A00]">দ্রুত ডেলিভারি</h3>
                <p class="text-gray-700 leading-relaxed font-medium">
                    ঢাকার ভিতরে ২৪ ঘন্টা এবং ঢাকার বাইরে ৪৮ ঘন্টার মধ্যে আপনার পছন্দের পণ্য পৌঁছে দেওয়ার নিশ্চয়তা।
                </p>
            </div>

            <!-- Feature 3: 24/7 Service -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 border-l-4 border-l-blue-500 group shadow-xs">
                <h3 class="text-xl font-bold text-blue-600 mb-4 tracking-tight">২৪/৭ গ্রাহক সেবা</h3>
                <p class="text-gray-700 leading-relaxed font-medium">
                    যেকোনো সমস্যায় আমাদের গ্রাহক সেবা টিম সর্বদা আপনার পাশে। ফোন, ইমেইল বা চ্যাটে যেকোনো সময় আমাদের পাবেন।
                </p>
            </div>

        </div>
    </section>
    <!-- JOIN US CTA SECTION -->
    <section class="container py-6 mx-auto">
        <div
            class="relative bg-gradient-to-br from-[#FFC185] to-[#E65C00] rounded-lg p-6 text-center shadow-sm shadow-orange-200 overflow-hidden group">
            <div class="relative z-10 max-w-3xl mx-auto">
                <!-- Heading -->
                <h2 class="text-lg md:text-2xl font-black text-white mb-6 tracking-tight leading-tight">
                    আমাদের সাথে যোগ দিন
                </h2>
                <!-- Description -->
                <p class="text-md text-white mb-10 leading-relaxed font-medium max-w-2xl">
                    আমাদের পরিবারের অংশ হয়ে উঠুন এবং পান বিশেষ ছাড়, নতুন পণ্যের আপডেট এবং এক্সক্লুসিভ অফার।
                </p>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-5 justify-center items-center">
                    <a href="/register"
                        class="w-full sm:w-auto bg-white text-[#FF6A00] px-10 py-4 rounded-xl font-black text-md hover:bg-orange-50 hover:scale-105 transition-all shadow-xl shadow-black/10">
                        অ্যাকাউন্ট তৈরি করুন
                    </a>
                    <a href="contact.html"
                        class="w-full sm:w-auto border-2 border-white text-white px-10 py-4 rounded-xl font-black text-md hover:bg-white hover:text-[#FF6A00] hover:scale-105 transition-all">
                        যোগাযোগ করুন
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
