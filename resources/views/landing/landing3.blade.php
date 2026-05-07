<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $landing->title }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Hind Siliguri', sans-serif;
            scroll-behavior: smooth;
        }

        .bg-brand-dark {
            background-color: #0D2601;
        }

        .bg-brand-green {
            background-color: #1f8a54;
        }

        .text-neon {
            color: #00FFCC;
        }

        .text-yellow-gold {
            color: #FFD700;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: #1f8a54;
            color: white;
            padding: 12px 35px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 1.4rem;
            transition: all 0.3s;
            box-shadow: 0 4px 0 #145a32;
        }

        .cta-button:hover {
            background: #176840;
            transform: translateY(-2px);
        }

        .cta-button:active {
            transform: translateY(2px);
            box-shadow: none;
        }

        /* Swiper Pagination Style */
        .rv-swiper .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: #d1d5db;
            opacity: 1;
        }

        .rv-swiper .swiper-pagination-bullet-active {
            background: #0D2601 !important;
            width: 30px;
            border-radius: 10px;
        }

        /* Dashed Border Box */
        .price-box-dashed {
            border: 3px dashed #7DF9FF;
            background: rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body class="text-gray-800 bg-white overflow-x-hidden">

    {{-- ══ SECTION 1: HERO  ══ --}}
    <section class="relative bg-[#0D2601] text-white pt-12 pb-24 md:pb-48 px-4 text-center overflow-hidden">
        <div class="container mx-auto relative z-10">

            {{-- 1. Logo - Responsive width --}}
            <div class="mb-8 md:mb-12">
                @if ($setup->logo)
                    <img src="{{ $setup->logo_url }}" alt="Logo"
                        class="mx-auto w-16 sm:w-18 md:w-20 object-contain">
                @else
                    <span
                        class="text-xl md:text-3xl font-black uppercase tracking-widest text-[#FFD700]">{{ $setup->shop_name }}</span>
                @endif
            </div>

            {{-- 2. Main Heading - Responsive text sizes --}}
            <h1 class="text-2xl sm:text-4xl md:text-6xl font-bold leading-tight mb-4 md:mb-6 px-4 md:px-40">
                চিয়া সিড পুষ্টিকর খাবার এতে আছে দুধের চেয়ে
            </h1>

            {{-- 3. Sub Heading with Highlight - Responsive colors and shadow --}}
            <h2 class="text-3xl sm:text-5xl md:text-6xl font-black mb-6 md:mb-10 leading-tight px-4 md:px-40">
                ৫ গুণ বেশি ক্যালসিয়াম প্রায় ৩০০ রোগের
                <span
                    class="text-[#00D05E] block lg:inline-block mt-2 lg:mt-0 drop-shadow-[0_0_15px_rgba(0,208,94,0.4)]">
                    ঔষধ
                </span>
            </h2>

            {{-- 4. Description - Responsive width and opacity --}}

            <p class="text-xs sm:text-base md:text-xl opacity-90 leading-relaxed font-medium px-2 px-4 md:px-80">
                এতে আছে প্রচুর ওমেগা-৩ ফ্যাটি অ্যাসিড, কোয়ারসেটিন, কেমফেরল, ক্লোরোজেনিক অ্যাসিড ও ক্যাফিক অ্যাসিড
                নামক অ্যান্টি-অক্সিডেন্ট, পটাশিয়াম, ম্যাগনেসিয়াম, আয়রন, ক্যালসিয়াম এবং দ্রবণীয় ও অদ্রবণীয়
                খাদ্য আঁশ।
            </p>


            {{-- 5. CTA Button - Responsive padding and font size --}}
            <div class="mb-6 md:mb-10">
                <a href="#order"
                    class="inline-flex items-center gap-2 md:gap-4 bg-[#00D05E] hover:bg-[#00B853] text-white px-6 sm:px-10 md:px-16 py-3 md:py-5 rounded-full font-black text-base sm:text-xl md:text-3xl transition-all active:translate-y-1 md:active:translate-y-2 active:shadow-none uppercase">
                    অর্ডার করতে ক্লিক করুন
                    <i class="fas fa-shopping-cart text-lg md:text-3xl ml-1"></i>
                </a>
            </div>
        </div>

        <div class="absolute bottom-[-1px] left-0 w-full overflow-hidden leading-[0]">
            <svg class="relative block w-full h-[30px] sm:h-[60px] md:h-[120px]" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 1440 320" preserveAspectRatio="none">
                <path fill="#ffffff" d="M0,0 C480,320 960,320 1440,0 L1440,320 L0,320 Z"></path>
            </svg>
        </div>
    </section>
    {{-- Video --}}
    <section class="py-16 px-4 text-center">
        <div class="w-full max-w-6xl mx-auto mt-6 mb-6">
            <div
                class="relative border-[8px] sm:border-[12px] md:border-[18px] border-[#35B11E] rounded-xl overflow-hidden bg-black shadow-2xl">
                <div class="relative aspect-video rounded-2xl overflow-hidden shadow-lg bg-black">

                    @if ($landing->video)
                        <video class="w-full h-full object-cover" controls playsinline
                            poster="{{ $landing->thumbnail_url }}">
                            <source src="{{ $landing->video_url }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        <img src="{{ $landing->thumbnail_url ?? asset('./images/default-thumbnail.jpg') }}"
                            class="w-full h-full object-cover" alt="{{ $landing->name }}">

                        <div
                            class="absolute inset-0 pointer-events-none flex flex-col justify-between p-3 md:p-5 bg-gradient-to-t from-black/70 via-transparent to-black/30">
                            <div></div>
                            <div class="flex justify-end">
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-6 md:mb-10">
            <a href="#order"
                class="inline-flex items-center gap-2 md:gap-4 bg-[#00D05E] hover:bg-[#00B853] text-white px-6 sm:px-10 md:px-16 py-3 md:py-5 rounded-full font-black text-base sm:text-xl md:text-3xl transition-all active:translate-y-1 md:active:translate-y-2 active:shadow-none uppercase">
                অর্ডার করতে ক্লিক করুন
                <i class="fas fa-shopping-cart text-lg md:text-3xl ml-1"></i>
            </a>
        </div>
    </section>

    {{-- ══ SECTION 2: BENEFITS LIST (Static as per image) ══ --}}
    <section class="py-16 px-4 bg-white">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-10">
                <span
                    class="bg-[#dcfce7] text-[#0D2601] px-8 py-3 rounded-full text-xl md:text-3xl font-black border-2 border-brand-green">
                    চিয়া সিড কেন খাবেন? খাওয়ার উপকারিতা
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3">
                @php
                    $benefits = [
                        'চিয়া সিডে আছে ওমেগা-৩, যা হৃদরোগের ঝুঁকি ও ক্ষতিকর কোলেস্টেরল কমাতেও সাহায্য করে',
                        'এটি শরীরের শক্তি ও কর্মক্ষমতা বাড়ায়',
                        'প্রচুর পরিমাণে অ্যান্টি-অক্সিডেন্ট থাকায় চিয়া সিড রোগ প্রতিরোধ ক্ষমতাকে আরও শক্তিশালী করে',
                        'মেটাবলিক সিস্টেমকে উন্নত করার মাধ্যমে এটি ওজন কমাতে সহায়তা করে',
                        'চিয়া সিড ব্লাড সুগার (রক্তের চিনি) স্বাভাবিক রাখে, যা ডায়াবেটিস হওয়ার ঝুঁকি কমায়',
                        'এতে আছে প্রচুর পরিমাণ ক্যালসিয়াম, যা হাড়ের স্বাস্থ্য রক্ষায় বিশেষ উপকারী',
                        'চিয়া সিড কোলন পরিষ্কার রাখে। ফলে কোলন ক্যান্সারের ঝুঁকি কমে',
                        'এটি শরীর থেকে টক্সিন (বিষাক্ত পদার্থ) বের করে দিতে সাহায্য করে',
                        'চিয়া সিড পেটের প্রদাহজনিত বা গ্যাসের সমস্যা দূর করে',
                        'ভালো ঘুম হতেও সাহায্য করে চিয়া সিড',
                        'চিয়া বীজ হাঁটু ও জয়েন্টের ব্যথা দূর করে',
                    ];
                @endphp
                @foreach ($benefits as $b)
                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border-l-8 border-brand-green">
                        <i class="fas fa-check text-white bg-brand-green p-1 rounded-full text-xs"></i>
                        <p class="text-lg md:text-xl font-bold text-gray-700">{{ $b }}</p>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-12">
                <a href="#order" class="cta-button">অর্ডার করতে ক্লিক করুন <i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
    </section>

    {{-- ══ SECTION 3: NUTRITION GRID (Static as per image) ══ --}}
    <section class="py-16 px-4 bg-[#f1fcf1]">
        <div class="max-w-6xl mx-auto text-center">
            <h2 class="text-2xl md:text-5xl font-black mb-16 text-gray-900 leading-tight">
                কেন চিয়া সিড পৃথিবীর ১ নাম্বার সুপারফুড? চলুন জেনে নেই তার <span
                    class="text-brand-green">পুষ্টিগুণ</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @php
                    $nutrition = [
                        [
                            't' => 'দুধের তুলনায় ৫ গুণ বেশি ক্যালসিয়াম।',
                            'img' => 'https://kidzfunbd.com/wp-content/uploads/2026/04/milk.jpg',
                        ],
                        [
                            't' => 'কলার থেকে ২ গুণ বেশি পটাশিয়াম।',
                            'img' => 'https://kidzfunbd.com/wp-content/uploads/2026/04/banana.jpg',
                        ],
                        [
                            't' => 'ব্রকলির তুলনায় ১৫ গুণ বেশি ম্যাগনেসিয়াম।',
                            'img' => 'https://kidzfunbd.com/wp-content/uploads/2026/04/broccoli.jpg',
                        ],
                        [
                            't' => 'পালং শাকের তুলনায় ৩ গুণ বেশি আয়রন।',
                            'img' => 'https://kidzfunbd.com/wp-content/uploads/2026/04/spinach.jpg',
                        ],
                        [
                            't' => 'স্যালমন মাছের তুলনায় ৮ গুণ বেশি ওমেগা-৩।',
                            'img' => 'https://kidzfunbd.com/wp-content/uploads/2026/04/fish.jpg',
                        ],
                        [
                            't' => 'কমলালেবুর থেকে ৭ গুণ বেশি ভিটামিন সি।',
                            'img' => 'https://kidzfunbd.com/wp-content/uploads/2026/04/orange.jpg',
                        ],
                    ];
                @endphp
                @foreach ($nutrition as $n)
                    <div class="bg-white rounded-2xl overflow-hidden border-2 border-brand-green shadow-xl">
                        <img src="{{ $n['img'] }}" class="h-52 w-full object-cover">
                        <div class="p-5 bg-brand-dark text-white font-bold text-xl">{{ $n['t'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-12">
                <a href="#order" class="cta-button">অর্ডার করতে ক্লিক করুন <i class="fas fa-shopping-cart"></i></a>
            </div>
        </div>
    </section>

    {{-- ══ SECTION 4: MID DESCRIPTION ══ --}}
    <section class="bg-brand-dark py-20 px-4 text-white text-center">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl md:text-5xl font-black mb-10 text-[#7DF9FF]">আমাদের এই চিয়া সিডের পুষ্টিগুণ</h2>
            <p class="text-lg md:text-2xl opacity-80 leading-relaxed mb-12">
                পুষ্টিবিদরা চিয়া সিডকে সুপারফুড নামে ডাকতে ভালোবাসেন। কারণ এতে আছে প্রচুর ওমেগা-৩ ফ্যাটি অ্যাসিড,
                কোয়ারসেটিন, কেমফেরল, ক্লোরোজেনিক অ্যাসিড ও ক্যাফিক অ্যাসিড। এতে আছে দুধের চেয়ে ৫ গুণ বেশি ক্যালসিয়াম,
                কলার চেয়ে ২ গুণ বেশি পটাশিয়াম।
            </p>

            <div class="mb-10 text-yellow-gold text-lg md:text-2xl font-bold">
                যে কোন তথ্যের জন্য যোগাযোগ করুন: <a href="tel:{{ $setup->phone }}">{{ $setup->phone }}</a>
            </div>

            <div class="price-box-dashed p-8 md:p-14 rounded-[50px] shadow-2xl">
                <p class="text-xl md:text-3xl font-bold mb-6 text-green-300">অফার চলাকালে আপনাদের পছন্দের ৫০০ গ্রাম
                    চিয়া সিড</p>
                <h3 class="text-5xl md:text-8xl font-black text-red-500">
                    মূল্য: = ৯৯০ টাকা।
                </h3>
            </div>
        </div>
    </section>

    {{-- ══ SECTION 5: WHY CHOOSE US ══ --}}
    <section class="py-16 px-4 bg-white text-center">
        <div class="max-w-6xl mx-auto">
            <h2
                class="text-2xl md:text-4xl font-bold mb-14 underline decoration-brand-green decoration-4 underline-offset-8">
                আমাদের কাছ থেকে <span class="text-brand-green">কেন কিনবেন?</span></h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @php $trust = ['কোয়ালিটি যাচাই', 'অরিজিনাল পণ্য', 'দ্রুত ডেলিভারি', '২৪/৭ সাপোর্ট']; @endphp
                @foreach ($trust as $t)
                    <div
                        class="p-6 bg-white border border-gray-100 shadow-xl rounded-2xl flex flex-col items-center gap-4">
                        <div
                            class="w-16 h-16 rounded-full bg-green-50 flex items-center justify-center text-brand-green text-3xl shadow-inner">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <p class="font-black text-xl text-gray-800">{{ $t }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══ SECTION 6: CUSTOMER REVIEW (Dynamic) ══ --}}
    <section class="py-16 bg-gray-50 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-2xl md:text-5xl font-black text-center mb-16 text-brand-dark">আমাদের কাস্টমার রিভিউ</h2>
            <div class="relative px-4 md:px-10">
                <div class="swiper rv-swiper">
                    <div class="swiper-wrapper">
                        @if ($landing->img_paths)
                            @foreach ($landing->img_paths as $img)
                                <div class="swiper-slide">
                                    <div class="bg-white p-2 rounded-3xl shadow-2xl border border-gray-100 mx-2 mb-12">
                                        <img src="{{ asset('storage/' . $img) }}"
                                            class="w-full h-auto rounded-2xl aspect-[4/3] object-cover">
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    {{-- ══ SECTION 7: ORDER FORM (Dynamic Component) ══ --}}
    <section id="order" class="py-20 px-4 bg-white border-t border-gray-100">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-6xl font-black text-red-600 italic">তাই আর দেরি না করে আজই অর্ডার করুন</h2>
        </div>
        <x-landing.order-form :landing="$landing" />
    </section>

    {{-- ══ FOOTER ══ --}}
    <footer class="bg-brand-dark py-12 px-4 text-center text-white border-t border-white/10">
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="text-sm md:text-base font-medium opacity-70">
                {{ $setup->address }} | Support: {{ $setup->phone }}
            </div>
            <div class="border-t border-white/5 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs opacity-50">Copyright © {{ date('Y') }} {{ $setup->shop_name }}. All Rights
                    Reserved.</p>
                <div class="flex gap-4 text-xs opacity-50">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms & Conditions</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swiper !== 'undefined') {
                new Swiper('.rv-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    loop: true,
                    autoplay: {
                        delay: 3500,
                        disableOnInteraction: false
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true
                    },
                    breakpoints: {
                        768: {
                            slidesPerView: 2
                        },
                        1024: {
                            slidesPerView: 3
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>
