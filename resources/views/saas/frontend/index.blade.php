@extends('saas.layouts.layout')

@section('content')
<style>
      .hero-bg {
        background: radial-gradient(circle at 70% 30%, #1e1b4b 0%, #0a061e 60%);
      }
      .hero-bg::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
          linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
          linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events: none;
      }
    </style>
    <!-- HERO SECTION -->
    <section class="hero-bg min-h-screen flex items-center pt-28 pb-32 md:pt-20 relative overflow-hidden">
        <div class="container mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <!-- Left Side: Content -->
            <div class="text-center lg:text-left order-2 lg:order-1">
                <h1 class="text-white text-2xl md:text-3xl lg:text-4xl font-bold leading-tight mb-6">
                    {{ $slider->title ?? 'আপনার ব্যবসার জন্য দরকারি সব কিছু এখন এক জায়গায়' }}
                </h1>
                <p class="text-gray-400 text-base md:text-lg lg:text-xl leading-relaxed mb-10 max-w-2xl mx-auto lg:mx-0">
                    {{ $slider->description ?? 'আপনার ব্যবসার জন্য দরকারি সব কিছু এখন এক জায়গায়' }}
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row flex-wrap gap-4 items-center justify-center lg:justify-start">
                    <a href="#"
                        class="w-full sm:w-auto bg-[#5c46e5] hover:bg-[#4a38b8] text-white px-8 py-4 rounded-xl font-bold text-lg transition shadow-lg shadow-indigo-500/20 text-center">
                        ফ্রি ট্রায়াল শুরু করুন
                    </a>
                    <a href="#"
                        class="w-full sm:w-auto bg-white/10 hover:bg-white/20 text-white border border-white/20 px-8 py-4 rounded-xl font-bold text-lg flex items-center justify-center gap-2 transition text-center">
                        ডেমো দেখুন <i class="fa-solid fa-play text-xs"></i>
                    </a>
                </div>

                <!-- Pagination Dots -->
                <div class="flex items-center justify-center lg:justify-start gap-2 mt-12 md:mt-16">
                    <span class="w-10 h-2.5 bg-white rounded-full"></span>
                    <span class="w-2.5 h-2.5 bg-white/30 rounded-full"></span>
                    <span class="w-2.5 h-2.5 bg-white/30 rounded-full"></span>
                    <span class="w-2.5 h-2.5 bg-white/30 rounded-full"></span>
                </div>
            </div>

            <!-- Right Side: Interactive Graphics -->
            <div class="relative flex justify-center items-center order-1 lg:order-2 py-20">
                <!-- Dashed Circles -->
                <div
                    class="absolute w-[280px] h-[280px] sm:w-[350px] sm:h-[350px] md:w-[400px] md:h-[400px] border border-white/10 rounded-full">
                </div>
                <div
                    class="absolute w-[200px] h-[200px] sm:w-[260px] sm:h-[260px] md:w-[320px] md:h-[320px] border border-white/10 rounded-full">
                </div>
                <div
                    class="absolute w-[140px] h-[140px] sm:w-[180px] sm:h-[180px] md:w-[240px] md:h-[240px] border border-white/10 rounded-full">
                </div>

                <!-- Central White Box Logo -->
                <div class="relative z-10 p-6 md:p-10 rounded-[35px] shadow-2xl float-anim">
                    <img src="{{ $slider->image_url ?? asset('./images/saas/hero.png') }}" class="w-16 h-16 md:w-[55vh] md:h-[40vh] object-contain"
                        alt="Core Platform" />
                </div>

                <!-- --- CUSTOMER REVIEW SECTION --- -->
                <div class="absolute -bottom-10 md:-bottom-10 flex flex-col items-center">
                    <svg class="w-12 h-16 md:w-16 md:h-24 text-white/40 mb-2" viewBox="0 0 50 100" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 5C25 35 35 65 30 90" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" />
                        <path d="M22 82L30 92L40 84" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <!-- Rating & Text -->
                    <div class="flex items-center gap-2">
                        <span class="text-[#fde047] text-xl md:text-2xl">★</span>
                        <span class="text-[#fde047] font-bold text-lg md:text-2xl">4.8</span>
                        <a href="#"
                            class="text-gray-300 text-sm md:text-xl underline decoration-gray-500 underline-offset-8 hover:text-white transition font-medium">
                            কাস্টমার রিভিউ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- LOGO SHOWCASE SECTION -->
    <section class="bg-black py-16 border-t border-white/5">
        <div class="container mx-auto">
            <div
                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 items-center justify-items-center gap-y-12 gap-x-8 grayscale hover:opacity-100 transition-all duration-500">
                <!-- Ghorer Bazar -->
                <div class="w-full flex justify-center">
                    <img src="{{ asset('./images/saas/ghore.png') }}" alt="Ghorer Bazar"
                        class="h-8 md:h-10 lg:h-14 object-contain hover:grayscale-0 transition cursor-pointer" />
                </div>

                <!-- Daraz -->
                <div class="w-full flex justify-center">
                    <img src="{{ asset('./images/saas/daraz.png') }}" alt="Daraz"
                        class="h-8 md:h-10 lg:h-14 object-contain hover:grayscale-0 transition cursor-pointer" />
                </div>

                <!-- Honeyraj -->
                <div class="w-full flex justify-center">
                    <img src="{{ asset('./images/saas/honeyraj.png') }}" alt="Honeyraj"
                        class="h-8 md:h-10 lg:h-14 object-contain hover:grayscale-0 transition cursor-pointer" />
                </div>

                <!-- Chaldal -->
                <div class="w-full flex justify-center">
                    <img src="{{ asset('./images/saas/chaldal.svg') }}" alt="Chaldal"
                        class="h-8 md:h-10 lg:h-14 object-contain hover:grayscale-0 transition cursor-pointer" />
                </div>

                <!-- bKash -->
                <div class="w-full flex justify-center">
                    <img src="{{ asset('./images/saas/bkash.png') }}" alt="bKash"
                        class="h-8 md:h-10 lg:h-11 object-contain hover:grayscale-0 transition cursor-pointer" />
                </div>

                <!-- Rokomari -->
                <div class="w-full flex justify-center">
                    <img src="{{ asset('./images/saas/rokomari.png') }}" alt="Rokomari"
                        class="h-8 md:h-10 lg:h-11 object-contain hover:grayscale-0 transition cursor-pointer" />
                </div>
            </div>
        </div>
    </section>
    <!-- FEATURES SECTION -->
    <section class="bg-white py-20">
        <div class="container mx-auto px-6 md:px-10">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <span
                    class="inline-block px-5 md:px-8 py-1.5 md:py-2.5 rounded-full border border-indigo-100 bg-indigo-50/50 text-indigo-600 font-semibold text-sm md:text-lg mb-6">
                    ফিচারসমূহ
                </span>
                <h2 class="text-2xl md:text-5xl font-bold text-gray-900 leading-tight">
                    আপনার ব্যবসার জন্য <br class="hidden md:block" />
                    দরকারি সব কিছু এখন এক জায়গায়
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 cursor-pointer">
                <!-- Feature Card 1 -->
                <div
                    class="bg-[#f9faff] p-8 md:p-12 rounded-[40px] border border-indigo-100 transition-all duration-300 group hover:shadow-xl hover:shadow-indigo-500/5">
                    <!-- Icon Area (Image এর মতো হুবহু বর্ডারসহ সার্কেল) -->
                    <div
                        class="w-20 h-20 rounded-full border border-indigo-300 bg-white flex items-center justify-center mb-8">
                        <i class="fa-solid fa-file-lines text-3xl text-[#5c46e5]"></i>
                    </div>

                    <!-- Content Area -->
                    <h3 class="text-2xl md:text-3xl font-bold text-[#5c46e5] mb-5">
                        সেলস ও POS
                    </h3>
                    <p class="text-gray-700 text-lg leading-relaxed mb-12">
                        অর্ডার, ইনভয়েস, রিটার্ন, POS সিস্টেম ও লাইভ সেলস ড্যাশবোর্ড
                    </p>

                    <!-- Link Area -->
                    <a href="#"
                        class="inline-flex items-center gap-3 font-bold text-gray-900 group-hover:text-[#5c46e5] transition-colors text-lg">
                        বিস্তারিত জানুন
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>

                <!-- Feature Card 2 -->
                <div
                    class="bg-[#f9faff] p-8 md:p-12 rounded-[40px] border border-indigo-100 transition-all duration-300 group hover:shadow-xl hover:shadow-indigo-500/5">
                    <div
                        class="w-20 h-20 rounded-full border border-indigo-300 bg-white flex items-center justify-center mb-8">
                        <i class="fa-solid fa-file-lines text-3xl text-[#5c46e5]"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold text-[#5c46e5] mb-5">
                        সেলস ও POS
                    </h3>
                    <p class="text-gray-700 text-lg leading-relaxed mb-12">
                        অর্ডার, ইনভয়েস, রিটার্ন, POS সিস্টেম ও লাইভ সেলস ড্যাশবোর্ড
                    </p>
                    <a href="#"
                        class="inline-flex items-center gap-3 font-bold text-gray-900 group-hover:text-[#5c46e5] transition-colors text-lg">
                        বিস্তারিত জানুন
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>
                <!-- Feature Card 2 -->
                <div
                    class="bg-[#f9faff] p-8 md:p-12 rounded-[40px] border border-indigo-100 transition-all duration-300 group hover:shadow-xl hover:shadow-indigo-500/5">
                    <div
                        class="w-20 h-20 rounded-full border border-indigo-300 bg-white flex items-center justify-center mb-8">
                        <i class="fa-solid fa-file-lines text-3xl text-[#5c46e5]"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold text-[#5c46e5] mb-5">
                        সেলস ও POS
                    </h3>
                    <p class="text-gray-700 text-lg leading-relaxed mb-12">
                        অর্ডার, ইনভয়েস, রিটার্ন, POS সিস্টেম ও লাইভ সেলস ড্যাশবোর্ড
                    </p>
                    <a href="#"
                        class="inline-flex items-center gap-3 font-bold text-gray-900 group-hover:text-[#5c46e5] transition-colors text-lg">
                        বিস্তারিত জানুন
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>
                <!-- Feature Card 2 -->
                <div
                    class="bg-[#f9faff] p-8 md:p-12 rounded-[40px] border border-indigo-100 transition-all duration-300 group hover:shadow-xl hover:shadow-indigo-500/5">
                    <div
                        class="w-20 h-20 rounded-full border border-indigo-300 bg-white flex items-center justify-center mb-8">
                        <i class="fa-solid fa-file-lines text-3xl text-[#5c46e5]"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold text-[#5c46e5] mb-5">
                        সেলস ও POS
                    </h3>
                    <p class="text-gray-700 text-lg leading-relaxed mb-12">
                        অর্ডার, ইনভয়েস, রিটার্ন, POS সিস্টেম ও লাইভ সেলস ড্যাশবোর্ড
                    </p>
                    <a href="#"
                        class="inline-flex items-center gap-3 font-bold text-gray-900 group-hover:text-[#5c46e5] transition-colors text-lg">
                        বিস্তারিত জানুন
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>
                <!-- Feature Card 2 -->
                <div
                    class="bg-[#f9faff] p-8 md:p-12 rounded-[40px] border border-indigo-100 transition-all duration-300 group hover:shadow-xl hover:shadow-indigo-500/5">
                    <div
                        class="w-20 h-20 rounded-full border border-indigo-300 bg-white flex items-center justify-center mb-8">
                        <i class="fa-solid fa-file-lines text-3xl text-[#5c46e5]"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold text-[#5c46e5] mb-5">
                        সেলস ও POS
                    </h3>
                    <p class="text-gray-700 text-lg leading-relaxed mb-12">
                        অর্ডার, ইনভয়েস, রিটার্ন, POS সিস্টেম ও লাইভ সেলস ড্যাশবোর্ড
                    </p>
                    <a href="#"
                        class="inline-flex items-center gap-3 font-bold text-gray-900 group-hover:text-[#5c46e5] transition-colors text-lg">
                        বিস্তারিত জানুন
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>

                <!-- Feature Card 3 -->
                <div
                    class="bg-[#f9faff] p-8 md:p-12 rounded-[40px] border border-indigo-100 transition-all duration-300 group hover:shadow-xl hover:shadow-indigo-500/5">
                    <div
                        class="w-20 h-20 rounded-full border border-indigo-300 bg-white flex items-center justify-center mb-8">
                        <i class="fa-solid fa-file-lines text-3xl text-[#5c46e5]"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold text-[#5c46e5] mb-5">
                        সেলস ও POS
                    </h3>
                    <p class="text-gray-700 text-lg leading-relaxed mb-12">
                        অর্ডার, ইনভয়েস, রিটার্ন, POS সিস্টেম ও লাইভ সেলস ড্যাশবোর্ড
                    </p>
                    <a href="#"
                        class="inline-flex items-center gap-3 font-bold text-gray-900 group-hover:text-[#5c46e5] transition-colors text-lg">
                        বিস্তারিত জানুন
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>
            </div>

            <!-- Bottom CTA Button -->
            <div class="mt-16 text-center">
                <button
                    class="bg-[#5c46e5] text-white px-10 py-4 rounded-xl font-bold hover:bg-[#4a38b8] transition shadow-lg shadow-indigo-100">
                    ফ্রি ট্রায়াল শুরু করুন
                </button>
            </div>
        </div>
    </section>
    <!-- INTEGRATION SECTION -->
    <section class="bg-[#f9faff] py-20 px-4 md:px-10 overflow-hidden">
        <div class="max-w-[1400px] mx-auto">
            <div class="text-center mb-16">
                <span
                    class="inline-block px-6 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#5c46e5] font-semibold text-[15px] mb-6">
                    ইন্টিগ্রেশন
                </span>
                <h2 class="text-2xl md:text-4xl font-extrabold text-gray-900 leading-tight max-w-4xl mx-auto">
                    আপনার পুরো ব্যবসা ট্র্যাক করুন, অটোমেট করুন
                    <br class="hidden md:block" />
                    এবং দ্রুত গ্রো করুন — একটি স্মার্ট সিস্টেম দিয়ে।
                </h2>
            </div>

            <!-- Integration Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Card 1: Payment Methods -->
                <div class="bg-white border border-indigo-100 rounded-2xl p-8 md:p-14">
                    <div class="flex flex-col items-center w-full">
                        <!-- Top Shopify Icon -->
                        <div
                            class="w-20 h-20 bg-[#5c46e5] rounded-full flex items-center justify-center shadow-lg shadow-indigo-200 z-10">
                            <i class="fa-brands fa-shopify text-white text-4xl"></i>
                        </div>

                        <!-- Line down to Middle Box -->
                        <div class="w-[2px] h-10 bg-[#5c46e5]"></div>

                        <!-- Middle Node Box -->
                        <div
                            class="px-8 py-3 border-2 border-[#5c46e5] rounded-2xl text-gray-900 font-bold text-base bg-white z-10">
                            পেমেন্ট মেথড
                        </div>

                        <!-- The Fork Connection Line -->
                        <div class="w-full relative flex flex-col items-center">
                            <!-- Vertical line from middle box to horizontal bar -->
                            <div class="w-[2px] h-10 bg-[#5c46e5]"></div>

                            <!-- Horizontal Bar: Exactly connects the centers of 1st and 3rd box -->
                            <div class="absolute bottom-0 w-[66.6%] h-[2px] bg-[#5c46e5]"></div>
                        </div>

                        <!-- 3 Vertical Lines down to logos -->
                        <div class="flex justify-between w-full px-[16.6%]">
                            <div class="w-[2px] h-10 bg-[#5c46e5]"></div>
                            <div class="w-[2px] h-10 bg-[#5c46e5]"></div>
                            <div class="w-[2px] h-10 bg-[#5c46e5]"></div>
                        </div>

                        <!-- Logo Row -->
                        <div class="grid grid-cols-3 gap-4 w-full">
                            <div
                                class="border-2 border-[#5c46e5] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/nagad.png') }}" class="h-8 md:h-10 object-contain" alt="Nagad"
                                    onerror="
                        this.src =
                          'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Nagad_Logo.svg/1200px-Nagad_Logo.svg.png'
                      " />
                            </div>
                            <div
                                class="border-2 border-[#5c46e5] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/bkash.png') }}"
                                    class="h-8 md:h-10 object-contain" alt="bKash" />
                            </div>
                            <div
                                class="border-2 border-[#5c46e5] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/sslcommerz.png') }}"
                                    class="h-5 md:h-7 object-contain" alt="SSL" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Courier Management -->
                <div class="bg-white border border-indigo-100 rounded-2xl p-8 md:p-14">
                    <div class="flex flex-col items-center w-full">
                        <!-- Top Shopify Icon -->
                        <div
                            class="w-20 h-20 bg-[#5c46e5] rounded-full flex items-center justify-center shadow-lg shadow-indigo-200 z-10">
                            <i class="fa-brands fa-shopify text-white text-4xl"></i>
                        </div>

                        <div class="w-[2px] h-10 bg-[#5c46e5]"></div>

                        <div
                            class="px-8 py-3 border-2 border-[#5c46e5] rounded-2xl text-gray-900 font-bold text-base bg-white z-10">
                            কুরিয়ার ম্যানেজমেন্ট
                        </div>

                        <div class="w-full relative flex flex-col items-center">
                            <div class="w-[2px] h-10 bg-[#5c46e5]"></div>
                            <div class="absolute bottom-0 w-[66.6%] h-[2px] bg-[#5c46e5]"></div>
                        </div>

                        <div class="flex justify-between w-full px-[16.6%]">
                            <div class="w-[2px] h-10 bg-[#5c46e5]"></div>
                            <div class="w-[2px] h-10 bg-[#5c46e5]"></div>
                            <div class="w-[2px] h-10 bg-[#5c46e5]"></div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 w-full">
                            <div
                                class="border-2 border-[#5c46e5] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/steadfast.png') }}"
                                    class="h-8 md:h-10 object-contain" alt="Steadfast" />
                            </div>
                            <div
                                class="border-2 border-[#5c46e5] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/pathao.png') }}"
                                    class="h-8 md:h-10 object-contain" alt="Pathao" />
                            </div>
                            <div
                                class="border-2 border-[#5c46e5] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <span class="font-black italic text-[#FF9900] text-sm md:text-lg">Carry<span
                                        class="text-[#000]">Bee</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- DEMO & TEMPLATE SECTION -->
    <section class="bg-white py-20 px-4 md:px-10">
        <div class="max-w-[1400px] mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <span
                    class="inline-block px-5 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#5c46e5] font-semibold text-sm md:text-lg mb-6">
                    ডেমো & টেমপ্লেট
                </span>
                <h2 class="text-2xl md:text-4xl font-black text-gray-900 leading-tight mb-10">
                    এক প্ল্যাটফর্মে পুরো সিস্টেম লাইভ এক্সপেরিয়েন্স নিন
                </h2>

                <!-- Tabs Container -->
                <div class="inline-flex p-1.5 bg-white border-2 border-indigo-100 rounded-2xl">
                    <button class="bg-[#5c46e5] text-white px-6 md:px-10 py-3 rounded-xl font-bold text-sm md:text-base">
                        ল্যান্ডিং পেজ টেমপ্লেট
                    </button>
                    <button
                        class="text-gray-900 px-6 md:px-10 py-3 rounded-xl font-bold text-sm md:text-base hover:bg-gray-50 transition">
                        ই-কমার্স টেমপ্লেট
                    </button>
                </div>
            </div>

            <!-- Templates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Template Card 1 -->
                <div class="group border border-indigo-100 rounded-xl overflow-hidden bg-white">
                    <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                        <!-- Template Preview Image -->
                        <img src="{{ asset('./images/saas/live1.png') }}"
                            class="w-full h-full object-cover object-top rounded-xl border border-gray-200 transition-transform duration-700 group-hover:scale-105"
                            alt="Preview" />
                    </div>
                    <div class="py-6 text-center border-t border-gray-100">
                        <a href="#"
                            class="text-xl md:text-2xl font-bold text-gray-900 underline hover:text-[#5c46e5] hover:decoration-[#5c46e5]">
                            Live Preview
                        </a>
                    </div>
                </div>

                <!-- Template Card 2 -->
                <div class="group border border-indigo-100 rounded-xl overflow-hidden bg-white">
                    <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                        <!-- Template Preview Image -->
                        <img src="{{ asset('./images/saas/live1.png') }}"
                            class="w-full h-full object-cover object-top rounded-xl border border-gray-200 transition-transform duration-700 group-hover:scale-105"
                            alt="Preview" />
                    </div>
                    <div class="py-6 text-center border-t border-gray-100">
                        <a href="#"
                            class="text-xl md:text-2xl font-bold text-gray-900 underline hover:text-[#5c46e5] hover:decoration-[#5c46e5]">
                            Live Preview
                        </a>
                    </div>
                </div>
                <!-- Template Card 3 -->
                <div class="group border border-indigo-100 rounded-xl overflow-hidden bg-white">
                    <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                        <!-- Template Preview Image -->
                        <img src="{{ asset('./images/saas/live1.png') }}"
                            class="w-full h-full object-cover object-top rounded-xl border border-gray-200 transition-transform duration-700 group-hover:scale-105"
                            alt="Preview" />
                    </div>
                    <div class="py-6 text-center border-t border-gray-100">
                        <a href="#"
                            class="text-xl md:text-2xl font-bold text-gray-900 underline hover:text-[#5c46e5] hover:decoration-[#5c46e5]">
                            Live Preview
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- INFINITY LOOP SECTION -->
    <section class="bg-white py-10 overflow-hidden scroll-container">
        <div class="space-y-6">
            <!-- Row 1: Moving Left -->
            <div class="animate-scroll-left flex gap-4">
                <!-- Badges (Repeat twice for seamless loop) -->
                <div class="flex gap-4">
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ফিটনেস</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গিফট
                        আইটেম</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">অর্গানিক
                        ফুড</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গ্যাজেট</span>
                    <span
                        class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইলেক্ট্রনিক্স</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">প্রসাধনী</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">হোম
                        ডেকোর</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইসলামিক</span>
                </div>
                <!-- Duplicate for Loop -->
                <div class="flex gap-4">
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ফিটনেস</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গিফট
                        আইটেম</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">অর্গানিক
                        ফুড</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গ্যাজেট</span>
                    <span
                        class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইলেক্ট্রনিক্স</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">প্রসাধনী</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">হোম
                        ডেকোর</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইসলামিক</span>
                </div>
            </div>

            <!-- Row 2: Moving Right -->
            <div class="animate-scroll-right flex gap-4">
                <div class="flex gap-4">
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গিফট
                        আইটেম</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">অর্গানিক
                        ফুড</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গ্যাজেট</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ফিটনেস</span>
                    <span
                        class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইলেক্ট্রনিক্স</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইসলামিক</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">প্রসাধনী</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">হোম
                        ডেকোর</span>
                </div>
                <!-- Duplicate for Loop -->
                <div class="flex gap-4">
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গিফট
                        আইটেম</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">অর্গানিক
                        ফুড</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গ্যাজেট</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ফিটনেস</span>
                    <span
                        class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইলেক্ট্রনিক্স</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইসলামিক</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">প্রসাধনী</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">হোম
                        ডেকোর</span>
                </div>
            </div>

            <!-- Row 3: Moving Left (Faster) -->
            <div class="animate-scroll-left flex gap-4" style="animation-duration: 35s">
                <div class="flex gap-4">
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ফিটনেস</span>
                    <span
                        class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইলেক্ট্রনিক্স</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">প্রসাধনী</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">হোম
                        ডেকোর</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গিফট
                        আইটেম</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">অর্গানিক
                        ফুড</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গ্যাজেট</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইসলামিক</span>
                </div>
                <!-- Duplicate for Loop -->
                <div class="flex gap-4">
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ফিটনেস</span>
                    <span
                        class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইলেক্ট্রনিক্স</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">প্রসাধনী</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">হোম
                        ডেকোর</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গিফট
                        আইটেম</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">অর্গানিক
                        ফুড</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">গ্যাজেট</span>
                    <span class="bg-[#5c46e5] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">ইসলামিক</span>
                </div>
            </div>
        </div>
    </section>
    <!-- SUCCESS SECTION (Dark Theme) -->
    <section class="bg-[#020410] py-24 px-6 md:px-10 relative overflow-hidden">
        <!-- Background Radial Glow (ঐচ্ছিক: ইমেজের মতো ডান কোণায় হালকা আভা) -->
        <div
            class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#5c46e5]/10 blur-[120px] rounded-full pointer-events-none">
        </div>

        <div class="container mx-auto">
            <!-- TOP PART: Header and Button -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-10 mb-20">
                <div class="max-w-2xl">
                    <!-- Badge Tag -->
                    <span
                        class="inline-block px-5 py-2 rounded-full border border-white/40 text-gray-300 text-sm md:text-lg font-medium mb-6">
                        আপনার অনলাইন ব্যবসার
                    </span>
                    <!-- Heading -->
                    <h2 class="text-white text-4xl md:text-5xl font-bold leading-tight mb-6">
                        সাফল্যের মূল কারিগর
                    </h2>
                    <!-- Description -->
                    <p class="text-gray-300 text-lg leading-relaxed max-w-2xl">
                        আমাদের অ্যাডভান্সড ইন্টিগ্রেশন আপনার ব্যবসায়িক কার্যক্রমকে করবে
                        আরও সহজ, স্মার্ট এবং কয়েকগুণ বেশি শক্তিশালী।
                    </p>
                </div>

                <!-- CTA Button -->
                <div class="flex-shrink-0">
                    <a href="#"
                        class="inline-block bg-[#5c46e5] hover:bg-[#4a38b8] text-white px-6 py-4 rounded-2xl font-bold text-md transition shadow-lg shadow-indigo-500/20">
                        ফ্রি ট্রায়াল শুরু করুন
                    </a>
                </div>
            </div>

            <!-- BOTTOM PART: 3 Columns Features -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-16 md:gap-12 lg:gap-20">
                <!-- Feature 1 -->
                <div class="group">
                    <div class="mb-6">
                        <i class="fa-solid fa-table-cells-large text-[#bfff3c] text-4xl"></i>
                    </div>
                    <h3 class="text-[#bfff3c] text-2xl font-bold mb-5">
                        সাশ্রয়ী প্রাইসিং
                    </h3>
                    <p class="text-gray-400 text-[17px] leading-relaxed mb-8">
                        আপনার ব্যবসার পরিধি অনুযায়ী বেছে নিন সঠিক প্ল্যান। কোনো লুকানো
                        চার্জ ছাড়াই পাচ্ছেন প্রিমিয়াম সব ফিচার।
                    </p>
                    <a href="#"
                        class="inline-flex items-center gap-3 text-white font-bold text-lg hover:text-[#bfff3c] transition group">
                        প্ল্যাটফর্মের সুবিধা দেখুন
                        <i class="fa-solid fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>

                <!-- Feature 2 -->
                <div class="group">
                    <div class="mb-6">
                        <i class="fa-solid fa-layer-group text-[#bfff3c] text-4xl"></i>
                    </div>
                    <h3 class="text-[#bfff3c] text-2xl font-bold mb-5">
                        ফিচারসমূহ এক্সপ্লোর করুন
                    </h3>
                    <p class="text-gray-400 text-[17px] leading-relaxed mb-8">
                        আপনার ব্যবসার পরিধি অনুযায়ী বেছে নিন সঠিক প্ল্যান। কোনো লুকানো
                        চার্জ ছাড়াই পাচ্ছেন প্রিমিয়াম সব ফিচার।
                    </p>
                    <a href="#"
                        class="inline-flex items-center gap-3 text-white font-bold text-lg hover:text-[#bfff3c] transition group">
                        এক্সপ্লোর করুন
                        <i class="fa-solid fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>

                <!-- Feature 3 -->
                <div class="group">
                    <div class="mb-6">
                        <i class="fa-solid fa-bolt text-[#bfff3c] text-4xl"></i>
                    </div>
                    <h3 class="text-[#bfff3c] text-2xl font-bold mb-5">
                        যাত্রা শুরু করুন আজই
                    </h3>
                    <p class="text-gray-400 text-[17px] leading-relaxed mb-8">
                        আপনার ব্যবসার পরিধি অনুযায়ী বেছে নিন সঠিক প্ল্যান। কোনো লুকানো
                        চার্জ ছাড়াই পাচ্ছেন প্রিমিয়াম সব ফিচার।
                    </p>
                    <a href="#"
                        class="inline-flex items-center gap-3 text-white font-bold text-lg hover:text-[#bfff3c] transition group">
                        শুরু করুন
                        <i class="fa-solid fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- SOLUTION SECTION -->
    <section class="bg-white py-20 px-4 md:px-10">
        <div class="container mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <span
                    class="inline-block px-5 py-1.5 rounded-full border border-indigo-100 bg-indigo-50/50 text-[#5c46e5] font-semibold text-sm md:text-lg mb-6">
                    অল-ইন-ওয়ান সলিউশন
                </span>
                <h2 class="text-2xl md:text-4xl font-extrabold text-gray-900 mb-10">
                    অপারেশন থেকে গ্রোথ — সবকিছু এক সিস্টেমে
                </h2>

                <!-- Segmented Tabs -->
                <div
                    class="inline-flex p-1.5 bg-indigo-50/30 border-2 border-indigo-100 gap-2 rounded-2xl w-full max-w-md">
                    <button class="flex-1 bg-[#5c46e5] text-white px-4 py-2.5 rounded-xl font-bold text-sm md:text-base">
                        ই-কমার্স
                    </button>
                    <button
                        class="flex-1 bg-indigo-100 text-gray-900 px-4 py-2.5 rounded-xl font-bold text-sm md:text-base hover:bg-white transition">
                        কর্পোরেট
                    </button>
                    <button
                        class="flex-1 bg-indigo-100 text-gray-900 px-4 py-2.5 rounded-xl font-bold text-sm md:text-base hover:bg-white transition">
                        POS
                    </button>
                </div>
            </div>

            <!-- Content Card -->
            <div class="bg-white border border-indigo-100 rounded-2xl p-8 md:p-16">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <!-- Left: Text Content -->
                    <div class="order-2 lg:order-1">
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-6">
                            ই-কমার্স সলিউশন
                        </h3>
                        <p class="text-gray-600 text-lg leading-relaxed mb-10 max-w-md">
                            আপনার অনলাইন স্টোরের প্রতিটি ভিজিটরকে দিন প্রিমিয়াম
                            এক্সপেরিয়েন্স। অটোমেটেড অর্ডার এবং ইনভেন্টরি ম্যানেজমেন্ট এখন
                            হাতের মুঠোয়।
                        </p>

                        <!-- Feature List -->
                        <div class="space-y-5 mb-12">
                            <div class="flex items-center gap-4 group">
                                <div
                                    class="w-8 h-8 bg-[#5c46e5] rounded-lg flex items-center justify-center text-white shadow-sm">
                                    <i class="fa-solid fa-display text-sm"></i>
                                </div>
                                <span class="font-bold text-gray-900 text-lg">ওয়েবসাইট ইন্টিগ্রেশন</span>
                            </div>
                            <div class="flex items-center gap-4 group">
                                <div
                                    class="w-8 h-8 bg-[#5c46e5] rounded-lg flex items-center justify-center text-white shadow-sm">
                                    <i class="fa-brands fa-apple text-lg"></i>
                                </div>
                                <span class="font-bold text-gray-900 text-lg">মোবাইল অ্যাপ সাপোর্ট</span>
                            </div>
                        </div>

                        <a href="#"
                            class="inline-flex items-center gap-3 bg-[#5c46e5] hover:bg-[#4a38b8] text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all shadow-lg shadow-indigo-100">
                            বিস্তারিত জানুন
                            <i class="fa-solid fa-arrow-right text-sm"></i>
                        </a>
                    </div>

                    <!-- Right: Browser Mockup -->
                    <div class="order-1 lg:order-2">
                        <div class="relative bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden group">
                            <!-- Browser Header -->
                            <div
                                class="bg-gray-50/50 border-b border-gray-200 px-4 py-3 flex items-center justify-between">
                                <div class="flex gap-2">
                                    <span class="w-3 h-3 bg-red-400 rounded-full"></span>
                                    <span class="w-3 h-3 bg-yellow-400 rounded-full"></span>
                                    <span class="w-3 h-3 bg-green-400 rounded-full"></span>
                                </div>
                                <div class="text-[10px] text-gray-400 font-mono tracking-widest uppercase">
                                    dashboard_preview_v2
                                </div>
                            </div>

                            <!-- Browser Content (Dashboard Skeleton) -->
                            <div class="p-6 bg-white min-h-[300px]">
                                <div class="grid grid-cols-4 gap-4 mb-6">
                                    <div class="h-20 bg-indigo-50 rounded-xl"></div>
                                    <div class="h-20 bg-gray-50 rounded-xl"></div>
                                    <div class="h-20 bg-gray-50 rounded-xl"></div>
                                    <div class="h-20 bg-gray-50 rounded-xl"></div>
                                </div>
                                <div class="h-4 w-1/3 bg-gray-100 rounded mb-3"></div>
                                <div class="h-4 w-1/2 bg-gray-50 rounded mb-8"></div>

                                <div
                                    class="border-2 border-dashed border-gray-100 rounded-2xl h-40 flex items-center justify-center">
                                    <span class="text-gray-300 font-bold text-xl">Ecommerce Preview</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY CHOOSE US SECTION (Updated with All Cards) -->

    <section class="bg-[#020410] py-24 px-6 md:px-10 relative overflow-hidden">
        <!-- Background Radial Glow -->
        <div
            class="absolute top-20 -left-20 w-[400px] h-[400px] bg-[#5c46e5]/10 blur-[100px] rounded-full pointer-events-none">
        </div>
        <div
            class="absolute bottom-20 -right-20 w-[400px] h-[400px] bg-[#5c46e5]/10 blur-[100px] rounded-full pointer-events-none">
        </div>

        <div class="container mx-auto">
            <!-- Section Title -->
            <div class="text-center mb-20">
                <h2 class="text-white text-3xl md:text-4xl font-extrabold">
                    কেন আমাদের সিস্টেম বেছে নেবেন?
                </h2>
            </div>

            <!-- Cards Container -->
            <div class="flex flex-col gap-10">
                <!-- Card 1: Super Fast Website -->
                <div class="bg-white rounded-2xl p-8 md:p-14 flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                    <div class="w-full lg:w-1/2 text-center lg:text-left">
                        <h3 class="text-[#5c46e5] text-3xl md:text-4xl font-extrabold mb-6">
                            সুপার ফাস্ট ওয়েবসাইট
                        </h3>
                        <p class="text-gray-800 text-lg leading-relaxed font-semibold max-w-xl">
                            আমাদের সিস্টেম হালকা ও অপ্টিমাইজড টেকনোলজিতে তৈরি, তাই আপনার
                            ওয়েবসাইট ও সফটওয়্যার সবসময় দ্রুত লোড হয়। কাস্টমারদের জন্য
                            স্মুথ এক্সপেরিয়েন্স নিশ্চিত করে এবং আপনার কনভার্সন রেট বাড়াতে
                            সাহায্য করে।
                        </p>
                    </div>
                    <div class="w-full lg:w-1/2">
                        <img src="./assets/images/choose.jpg" class="w-full h-auto rounded-3xl shadow-lg"
                            alt="Super Fast" />
                    </div>
                </div>

                <!-- Card 2: Unlimited Landing Page -->
                <div
                    class="bg-white rounded-2xl p-8 md:p-14 flex flex-col lg:flex-row-reverse items-center gap-12 lg:gap-20">
                    <div class="w-full lg:w-1/2 text-center lg:text-left">
                        <h3 class="text-[#5c46e5] text-3xl md:text-4xl font-extrabold mb-6">
                            আনলিমিটেড ল্যান্ডিং পেজ
                        </h3>
                        <p class="text-gray-800 text-lg leading-relaxed font-semibold max-w-xl">
                            আপনি চাইলে যত খুশি ল্যান্ডিং পেজ তৈরি করতে পারবেন—প্রতিটি
                            প্রোডাক্ট, ক্যাম্পেইন বা অফারের জন্য আলাদা পেজ। কোনো লিমিট
                            নেই, ফলে আপনার মার্কেটিং হবে আরও ফ্লেক্সিবল ও পাওয়ারফুল।
                        </p>
                    </div>
                    <div class="w-full lg:w-1/2">
                        <img src="./assets/images/choose.jpg" class="w-full h-auto rounded-3xl shadow-lg"
                            alt="Landing Page" />
                    </div>
                </div>

                <!-- Card 3: Easy Checkout System -->
                <div class="bg-white rounded-2xl p-8 md:p-14 flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                    <div class="w-full lg:w-1/2 text-center lg:text-left">
                        <h3 class="text-[#5c46e5] text-3xl md:text-4xl font-extrabold mb-6">
                            ইজি চেকআউট সিস্টেম
                        </h3>
                        <p class="text-gray-600 text-lg leading-relaxed font-semibold max-w-xl">
                            আপনি চাইলে যত খুশি ল্যান্ডিং পেজ তৈরি করতে পারবেন—প্রতিটি
                            প্রোডাক্ট, ক্যাম্পেইন বা অফারের জন্য আলাদা পেজ। কোনো লিমিট
                            নেই, ফলে আপনার মার্কেটিং হবে আরও ফ্লেক্সিবল ও পাওয়ারফুল।
                        </p>
                    </div>
                    <div class="w-full lg:w-1/2">
                        <img src="./assets/images/choose.jpg" class="w-full h-auto rounded-3xl shadow-lg"
                            alt="Checkout" />
                    </div>
                </div>

                <!-- NEW Card 4: Cost Effective Solution (Image Left, Text Right) -->
                <div class="bg-white rounded-2xl p-8 md:p-14 flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                    <div class="w-full lg:w-1/2">
                        <img src="./assets/images/choose.jpg" class="w-full h-auto rounded-3xl shadow-lg"
                            alt="Cost Effective" />
                    </div>
                    <div class="w-full lg:w-1/2 text-center lg:text-left">
                        <h3 class="text-[#5c46e5] text-3xl md:text-4xl font-extrabold mb-6">
                            কস্ট ইফেক্টিভ সলিউশন
                        </h3>
                        <p class="text-gray-800 text-lg leading-relaxed font-semibold max-w-xl">
                            আপনি চাইলে যত খুশি ল্যান্ডিং পেজ তৈরি করতে পারবেন—প্রতিটি
                            প্রোডাক্ট, ক্যাম্পেইন বা অফারের জন্য আলাদা পেজ। কোনো লিমিট
                            নেই, ফলে আপনার মার্কেটিং হবে আরও ফ্লেক্সিবল ও পাওয়ারফুল।
                        </p>
                    </div>
                </div>

                <!-- NEW Card 5: User-friendly Dashboard (Text Left, Image Right) -->
                <div class="bg-white rounded-2xl p-8 md:p-14 flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                    <div class="w-full lg:w-1/2 text-center lg:text-left">
                        <h3 class="text-[#5c46e5] text-3xl md:text-4xl font-extrabold mb-6">
                            ইউজার-ফ্রেন্ডলি ড্যাশবোর্ড
                        </h3>
                        <p class="text-gray-600 text-lg leading-relaxed font-semibold max-w-xl">
                            আপনি চাইলে যত খুশি ল্যান্ডিং পেজ তৈরি করতে পারবেন—প্রতিটি
                            প্রোডাক্ট, ক্যাম্পেইন বা অফারের জন্য আলাদা পেজ। কোনো লিমিট
                            নেই, ফলে আপনার মার্কেটিং হবে আরও ফ্লেক্সিবল ও পাওয়ারফুল।
                        </p>
                    </div>
                    <div class="w-full lg:w-1/2">
                        <img src="./assets/images/choose.jpg" class="w-full h-auto rounded-3xl shadow-lg"
                            alt="Dashboard" />
                    </div>
                </div>
            </div>

            <!-- FINAL CTA SECTION (ইমেজের নিচের টেক্সট এবং বাটন) -->
            <div class="mt-28 text-center">
                <h2 class="text-white text-2xl md:text-3xl font-semibold mb-10 leading-tight">
                    একটি স্মার্ট সিস্টেম—যা আপনার ব্যবসাকে আরও দ্রুত, সহজ ও লাভজনক করে
                    তোলে।
                </h2>
                <a href="#"
                    class="inline-block bg-[#5c46e5] hover:bg-[#4a38b8] text-white px-10 py-4 rounded-xl font-bold text-lg transition shadow-lg shadow-indigo-500/20">
                    ফ্রি ট্রায়াল শুরু করুন
                </a>
            </div>
        </div>
    </section>
    <!-- BLOG & INSIGHTS SECTION -->
    <section class="bg-white py-20 px-6 md:px-10">
        <div class="container mx-auto">
            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-12 gap-6">
                <h2 class="text-2xl md:text-4xl font-black text-gray-900">
                    ব্লগ ও ইনসাইটস
                </h2>
                <a href="#"
                    class="bg-[#5c46e5] text-white px-8 py-3 rounded-xl font-bold text-lg flex items-center gap-2 hover:bg-[#4a38b8] transition shadow-lg shadow-indigo-100">
                    আরও পড়ুন <i class="fa-solid fa-arrow-right text-sm"></i>
                </a>
            </div>

            <!-- Blog Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Blog Card 1 -->
                <div
                    class="bg-white border border-gray-100 rounded-2xl overflow-hidden group hover:shadow-xl transition-all duration-300">
                    <!-- Image Placeholder -->
                    <div class="aspect-[16/10] bg-[#eef2ff] relative overflow-hidden">
                        <!-- আসল ইমেজ থাকলে এখানে বসাবেন -->
                        <!-- <img src="your-image.jpg" class="w-full h-full object-cover"> -->
                    </div>

                    <!-- Content Area -->
                    <div class="p-6 md:p-8">
                        <!-- Meta info -->
                        <div class="flex justify-between items-center mb-5">
                            <span class="bg-gray-100 text-gray-700 px-4 py-1 rounded-full text-xs font-bold">
                                ব্যবসা
                            </span>
                            <div class="flex items-center gap-2 text-[#5c46e5] text-sm font-bold">
                                <i class="fa-regular fa-clock"></i>
                                <span>৫ মিনিট</span>
                            </div>
                        </div>

                        <!-- Headline -->
                        <h3 class="text-xl md:text-2xl font-semibold text-gray-900 mb-8 leading-tight">
                            কন্টেন্ট ওয়ার্কফ্লো: প্রসেস, স্টেপস এবং ব্যবসার জন্য এর
                            সুবিধা।
                        </h3>

                        <!-- Read More Link -->
                        <a href="#"
                            class="inline-flex items-center gap-2 text-[#5c46e5] font-bold text-lg group-hover:gap-3 transition-all">
                            বিস্তারিত পড়ুন
                            <i class="fa-solid fa-arrow-right text-sm"></i>
                        </a>
                    </div>
                </div>

                <!-- Blog Card 2 (Same as Card 1) -->
                <div
                    class="bg-white border border-gray-100 rounded-2xl overflow-hidden group hover:shadow-xl transition-all duration-300">
                    <div class="aspect-[16/10] bg-[#eef2ff]"></div>
                    <div class="p-6 md:p-8">
                        <div class="flex justify-between items-center mb-5">
                            <span class="bg-gray-100 text-gray-700 px-4 py-1 rounded-full text-xs font-bold">ব্যবসা</span>
                            <div class="flex items-center gap-2 text-[#5c46e5] text-sm font-bold">
                                <i class="fa-regular fa-clock"></i><span>৫ মিনিট</span>
                            </div>
                        </div>
                        <h3 class="text-xl md:text-2xl font-semibold text-gray-900 mb-8 leading-tight">
                            কন্টেন্ট ওয়ার্কফ্লো: প্রসেস, স্টেপস এবং ব্যবসার জন্য এর
                            সুবিধা।
                        </h3>
                        <a href="#"
                            class="inline-flex items-center gap-2 text-[#5c46e5] font-bold text-lg group-hover:gap-3 transition-all">
                            বিস্তারিত পড়ুন <i class="fa-solid fa-arrow-right text-sm"></i>
                        </a>
                    </div>
                </div>

                <!-- Blog Card 3 (Same as Card 1) -->
                <div
                    class="bg-white border border-gray-100 rounded-2xl overflow-hidden group hover:shadow-xl transition-all duration-300">
                    <div class="aspect-[16/10] bg-[#eef2ff]"></div>
                    <div class="p-6 md:p-8">
                        <div class="flex justify-between items-center mb-5">
                            <span class="bg-gray-100 text-gray-700 px-4 py-1 rounded-full text-xs font-bold">ব্যবসা</span>
                            <div class="flex items-center gap-2 text-[#5c46e5] text-sm font-bold">
                                <i class="fa-regular fa-clock"></i><span>৫ মিনিট</span>
                            </div>
                        </div>
                        <h3 class="text-xl md:text-2xl font-semibold text-gray-900 mb-8 leading-tight">
                            কন্টেন্ট ওয়ার্কফ্লো: প্রসেস, স্টেপস এবং ব্যবসার জন্য এর
                            সুবিধা।
                        </h3>
                        <a href="#"
                            class="inline-flex items-center gap-2 text-[#5c46e5] font-bold text-lg group-hover:gap-3 transition-all">
                            বিস্তারিত পড়ুন <i class="fa-solid fa-arrow-right text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- TESTIMONIAL SECTION -->
    <section class="bg-[#f9faff] py-20 px-6 md:px-10">
        <div class="container mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <span
                    class="inline-block px-5 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#5c46e5] font-semibold text-sm md:text-lg mb-6">
                    কাস্টমার রিভিউ
                </span>
                <h2 class="text-3xl md:text-5xl font-black text-gray-900">
                    আমাদের গ্রাহকদের মতামত
                </h2>
            </div>

            <!-- Masonry Grid (ইমেজের মতো হুবহু লেআউট) -->
            <div class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6">
                <!-- Card 1 (Short) -->
                <div
                    class="break-inside-avoid bg-white border-1 border-indigo-100 p-8 rounded-2xl hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="text-[#5c46e5] font-bold text-lg">
                                @ Foysal Mahmud
                            </h4>
                            <p class="text-gray-500 text-xs">CEO: uMart Bangladesh</p>
                        </div>
                        <div class="flex items-center gap-1 text-gray-900 font-bold">
                            <i class="fa-regular fa-star"></i> <span>4.8</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2 (Long - Content Added) -->
                <div
                    class="break-inside-avoid bg-white border-1 border-indigo-100 p-8 rounded-2xl hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h4 class="text-[#5c46e5] font-bold text-lg">
                                @ Foysal Mahmud
                            </h4>
                            <p class="text-gray-500 text-xs">CEO: uMart Bangladesh</p>
                        </div>
                        <div class="flex items-center gap-1 text-gray-900 font-bold">
                            <i class="fa-regular fa-star"></i> <span>4.8</span>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed text-[15px]">
                        “এই সফটওয়্যার ব্যবহার করার পর আমার পুরো ব্যবসা অনেক সহজ হয়ে
                        গেছে।” আগে আলাদা আলাদা সিস্টেম ব্যবহার করতাম, এখন সবকিছু এক
                        জায়গায় পাচ্ছি— অর্ডার, স্টক, অ্যাকাউন্টিং সব। টাইম সেভ হচ্ছে
                        আর ভুল কমে গেছে। “এই সফটওয়্যার ব্যবহার করার পর আমার পুরো ব্যবসা
                        অনেক সহজ হয়ে গেছে।” আগে আলাদা আলাদা সিস্টেম ব্যবহার করতাম, এখন
                        সবকিছু এক জায়গায় পাচ্ছি— অর্ডার, স্টক, অ্যাকাউন্টিং সব। টাইম
                        সেভ হচ্ছে আর ভুল কমে গেছে।
                    </p>
                </div>

                <!-- Card 3 (Short) -->
                <div
                    class="break-inside-avoid bg-white border-1 border-indigo-100 p-8 rounded-2xl hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-[#5c46e5] font-bold text-lg">
                                @ Foysal Mahmud
                            </h4>
                            <p class="text-gray-500 text-xs">CEO: uMart Bangladesh</p>
                        </div>
                        <div class="flex items-center gap-1 text-gray-900 font-bold">
                            <i class="fa-regular fa-star"></i> <span>4.8</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4 (Medium) -->
                <div
                    class="break-inside-avoid bg-white border-1 border-indigo-100 p-8 rounded-2xl hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h4 class="text-[#5c46e5] font-bold text-lg">
                                @ Foysal Mahmud
                            </h4>
                            <p class="text-gray-500 text-xs">CEO: uMart Bangladesh</p>
                        </div>
                        <div class="flex items-center gap-1 text-gray-900 font-bold">
                            <i class="fa-regular fa-star"></i> <span>4.8</span>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed text-[15px]">
                        অর্ডার, স্টক, অ্যাকাউন্টিং সব। টাইম সেভ হচ্ছে আর ভুল কমে গেছে।
                        চমৎকার সিস্টেম!
                    </p>
                </div>

                <!-- Card 5 (Long) -->
                <div
                    class="break-inside-avoid bg-white border-1 border-indigo-100 p-8 rounded-2xl hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h4 class="text-[#5c46e5] font-bold text-lg">
                                @ Foysal Mahmud
                            </h4>
                            <p class="text-gray-500 text-xs">CEO: uMart Bangladesh</p>
                        </div>
                        <div class="flex items-center gap-1 text-gray-900 font-bold">
                            <i class="fa-regular fa-star"></i> <span>4.8</span>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed text-[15px]">
                        আগে আলাদা আলাদা সিস্টেম ব্যবহার করতাম, এখন সবকিছু এক জায়গায়
                        পাচ্ছি— অর্ডার, স্টক, অ্যাকাউন্টিং সব। টাইম সেভ হচ্ছে আর ভুল কমে
                        গেছে।
                    </p>
                </div>

                <!-- Card 6 (Short) -->
                <div
                    class="break-inside-avoid bg-white border-1 border-indigo-100 p-8 rounded-2xl hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-[#5c46e5] font-bold text-lg">
                                @ Foysal Mahmud
                            </h4>
                            <p class="text-gray-500 text-xs">CEO: uMart Bangladesh</p>
                        </div>
                        <div class="flex items-center gap-1 text-gray-900 font-bold">
                            <i class="fa-regular fa-star"></i> <span>4.8</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom CTA Button -->
            <div class="mt-16 text-center">
                <a href="#"
                    class="inline-block bg-[#5c46e5] text-white px-10 py-3 rounded-xl font-bold hover:bg-[#4a38b8] transition shadow-lg shadow-indigo-100">
                    আরও দেখুন
                </a>
            </div>
        </div>
    </section>
@endsection
