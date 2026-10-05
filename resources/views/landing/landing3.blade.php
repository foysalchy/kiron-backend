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

        .hero-rich-content p {
            margin-bottom: 1rem;
            line-height: 1.4;
            font-size: 1rem;
        }

        .hero-rich-content strong {
            font-weight: 800;
            display: inline-block;
            font-size: 1.5rem;
        }

        @media (min-width: 768px) {
            .hero-rich-content p {
                font-size: 1.25rem;
            }

            .hero-rich-content strong {
                font-size: 3.5rem;
                letter-spacing: -1px;
            }
        }

        .hero-rich-content span {
            display: inline-block;
        }
    </style>
</head>

<body class="text-gray-800 bg-white overflow-x-hidden">

    {{-- ══ SECTION 1: HERO (Dynamic Version) ══ --}}
    <section class="relative bg-[#0D2601] text-white pt-12 pb-24 md:pb-48 px-4 text-center overflow-hidden">
        <div class="container mx-auto relative z-10">

            {{-- 1. Logo --}}
            <div class="mb-8 md:mb-12">
                @if ($setup->logo)
                    <img src="{{ $setup->logo_url }}" alt="Logo"
                        class="mx-auto w-16 sm:w-18 md:w-24 object-contain">
                @else
                    <span class="text-xl md:text-3xl font-black uppercase tracking-widest text-[#FFD700]">
                        {{ $setup->shop_name }}
                    </span>
                @endif
            </div>

            {{-- 2. Dynamic Short Description --}}
            <div class="hero-rich-content mb-8 md:mb-12 mx-auto max-w-6xl">
                <div class="prose-container">
                    {!! $landing->short_description !!}
                </div>
            </div>

            {{-- 3. CTA Button --}}
            <div class="mb-6 md:mb-10">
                <a href="#order"
                    class="inline-flex items-center gap-2 md:gap-4 bg-[#00D05E] hover:bg-[#00B853] text-[#0D2601] px-8 sm:px-10 md:px-16 py-3 md:py-5 rounded-full font-black text-lg sm:text-xl md:text-3xl transition-all active:translate-y-1 md:active:translate-y-2 active:shadow-none uppercase shadow-lg shadow-green-900/20">
                    অর্ডার করতে ক্লিক করুন
                    <i class="fas fa-shopping-cart text-lg md:text-3xl ml-1"></i>
                </a>
            </div>
        </div>

        {{-- Bottom Curve Shape --}}
        <div class="absolute bottom-[-1px] left-0 w-full overflow-hidden leading-[0]">
            <svg class="relative block w-full h-[30px] sm:h-[60px] md:h-[120px]" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 1440 320" preserveAspectRatio="none">
                <path fill="#ffffff" d="M0,0 C480,320 960,320 1440,0 L1440,320 L0,320 Z"></path>
            </svg>
        </div>
    </section>
    {{-- Video Section --}}
    <section class="py-10 md:py-16 px-4 text-center bg-white">
        <div class="w-full max-w-7xl mx-auto mb-10">
            <div
                class="relative border-[6px] sm:border-[10px] md:border-[15px] border-[#F1F1F1] rounded-2xl overflow-hidden bg-black shadow-2xl">

                <div class="relative w-full h-[200px] sm:h-[350px] md:h-[450px] overflow-hidden bg-black">

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
                            class="absolute inset-0 pointer-events-none bg-gradient-to-t from-black/60 via-transparent to-black/20">
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- CTA Button --}}
        <div class="mb-4">
            <a href="#order"
                class="inline-flex items-center gap-2 md:gap-4 bg-[#00D05E] hover:bg-[#00B853] text-[#0D2601] px-8 sm:px-12 py-3 md:py-5 rounded-full font-black text-lg sm:text-2xl md:text-3xl transition-all uppercase">
                অর্ডার করতে ক্লিক করুন
                <i class="fas fa-shopping-cart text-xl md:text-3xl ml-1"></i>
            </a>
        </div>
    </section>

    {{-- ══ SECTION 2: BENEFITS LIST ══ --}}
    <section class="py-16 px-4 bg-white">
        <div class="max-w-7xl mx-auto">

            <div class="text-center mb-10">
                <span
                    class="bg-[#dcfce7] text-[#0D2601] px-8 md:px-16 py-3 rounded-full text-xl md:text-4xl font-black border border-[#1f8a54]">
                    {{ data_get($landing->extras, 'features_title', 'চিয়া সিড কেন খাবেন? খাওয়ার উপকারিতা') }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3">
                @php
                    $dynamicBenefits = data_get($landing->extras, 'features') ?? [
                        'চিয়া সিডে আছে ওমেগা-৩, যা হৃদরোগের ঝুঁকি ও ক্ষতিকর কোলেস্টেরল কমাতেও সাহায্য করে',
                        'এটি শরীরের শক্তি ও কর্মক্ষমতা বাড়ায়',
                        'প্রচুর পরিমাণে অ্যান্টি-অক্সিডেন্ট থাকায় চিয়া সিড রোগ প্রতিরোধ ক্ষমতাকে আরও শক্তিশালী করে',
                        'মেটাবলিক সিস্টেমকে উন্নত করার মাধ্যমে এটি ওজন কমাতে সহায়তা করে',
                    ];
                @endphp

                @foreach ($dynamicBenefits as $benefit)
                    <div
                        class="flex items-center gap-3 p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-check text-white bg-[#1f8a54] p-1 rounded-full text-[10px] shrink-0"></i>

                        <p class="text-lg md:text-3xl text-gray-700 font-medium">
                            {{ $benefit }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mb-4 text-center mt-8 md:mt-12">
                <a href="#order"
                    class="inline-flex items-center gap-2 md:gap-4 bg-[#00D05E] hover:bg-[#00B853] text-[#0D2601] px-8 sm:px-12 py-3 md:py-5 rounded-full font-black text-lg sm:text-2xl md:text-3xl transition-all uppercase">
                    অর্ডার করতে ক্লিক করুন
                    <i class="fas fa-shopping-cart text-xl md:text-3xl ml-1"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- ══ SECTION 3: NUTRITION GRID  ══ --}}
    <section class="py-16 px-4 bg-white">
        <div class="max-w-7xl mx-auto text-center">

            <div
                class="inline-block bg-[#dcfce7] px-6 md:px-10 py-6 md:py-8 rounded-[40px] md:rounded-[60px] mb-12 border border-[#c1e9cd]">
                <h2 class="text-xl md:text-4xl font-bold text-[#0D2601] leading-tight max-w-6xl">

                    {!! data_get(
                        $landing->extras,
                        'features2_title',
                        'কেন চিয়া সিড পৃথিবীর ১ নাম্বার সুপারফুড?',
                    ) !!}
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                @php
                    $dynamicNutrition = data_get($landing->extras, 'features2') ?? [
                        'দুধের তুলনায় ৫ গুণ বেশি ক্যালসিয়াম।',
                        'কলার থেকে ২ গুণ বেশি পটাশিয়াম।',
                        'ব্রকলির তুলনায় ১৫ গুণ বেশি ম্যাগনেসিয়াম।',
                    ];
                @endphp

                @foreach ($dynamicNutrition as $item)
                    <div
                        class="bg-[#1D4935] text-white p-8 md:p-10 rounded-xl flex items-center justify-center min-h-[120px] md:min-h-[150px] shadow-lg transition-transform hover:scale-[1.02]">
                        <p class="text-lg md:text-2xl font-bold leading-snug">
                            {{ $item }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- ৩. বাটন --}}
            <div class="text-center mt-12">
                <a href="#order"
                    class="inline-flex items-center gap-2 md:gap-4 bg-[#00D05E] hover:bg-[#00B853] text-[#0D2601] px-8 md:px-14 py-3 md:py-5 rounded-full font-black text-lg md:text-3xl transition-all uppercase">
                    অর্ডার করতে ক্লিক করুন
                    <i class="fas fa-shopping-cart text-xl md:text-3xl ml-1"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- ══ SECTION 4: MID DESCRIPTION & PRICE (Pure Tailwind Dynamic) ══ --}}
    <section class="bg-[#1D4935] py-16 md:py-24 px-4 text-center text-white">
        <div class="max-w-7xl mx-auto">

            <div
                class="mb-12
            [&_h3]:text-2xl [&_h3]:md:text-5xl [&_h3]:font-bold [&_h3]:mb-10 [&_h3]:inline-block [&_h3]:pb-2 [&_h3]:uppercase
            [&_p]:text-sm [&_p]:md:text-xl [&_p]:leading-relaxed [&_p]:opacity-90 [&_p]:max-w-7xl [&_p]:mx-auto [&_p]:text-start [&_p]:font-medium">

                {!! $landing->description !!}
            </div>

            <hr class="border-[#00D05E]/40 mb-12 max-w-5xl mx-auto">

            <div class="mb-12 space-y-4">
                <p class="text-[#CCFF00] font-bold text-lg md:text-3xl tracking-wide uppercase">
                    যে কোন তথ্যের জন্য যোগাযোগ করুন
                </p>
                <p class="text-white font-black text-2xl md:text-3xl">
                    মোবাইল:
                    <a href="tel:{{ $setup->phone }}" class="hover:text-[#CCFF00] transition-all">
                        {{ $setup->phone }}
                    </a>
                </p>
            </div>

            <div
                class="inline-block bg-[#225A40] px-8 md:px-16 py-4 md:py-7 rounded-full mb-12 shadow-inner border border-white/10">
                <p class="text-[#CCFF00] font-bold text-lg md:text-4xl">
                    {{ $landing->title }}
                </p>
            </div>

            <h3
                class="text-4xl md:text-4xl font-black text-yellow-400 drop-shadow-[0_10px_20px_rgba(0,0,0,0.5)] tracking-tighter">
                {{ $landing->discount_price }}
            </h3>

        </div>
    </section>
  {{-- ══ SECTION 5: WHY CHOOSE US ══ --}}
<section class="py-16 px-4 bg-white text-center">
    <div class="max-w-7xl mx-auto">

        <div class="inline-block bg-[#dcfce7] px-8 md:px-20 py-4 rounded-full mb-16 border border-[#c1e9cd]">
            <h2 class="text-xl md:text-4xl font-bold text-[#0D2601] leading-tight">
                আমাদের কাছ থেকে <span class="text-[#00B22C]">কেন কিনবেন?</span>
            </h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">

            @php
                $featureKeys = ['features3', 'features4', 'features5', 'features6'];
            @endphp

            @foreach ($featureKeys as $key)
                @php
                    $item = data_get($landing->extras, $key . '.0');
                @endphp

                @if($item)
                <div class="bg-white rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.06)] border-t-[8px] md:border-t-[12px] border-[#00B22C] p-5 md:p-8 flex flex-col items-center justify-center transition-all hover:-translate-y-2 hover:shadow-2xl">

                    <div class="w-16 h-16 md:w-24 md:h-24 mb-6">
                        @if(!empty($item['image']))
                            <img src="{{ asset('storage/' . $item['image']) }}"
                                 class="w-full h-full object-contain"
                                 alt="{{ $item['title'] ?? 'Feature' }}">
                        @else
                            <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" class="w-full h-full object-contain opacity-20">
                        @endif
                    </div>

                    <h3 class="text-md md:text-xl font-extrabold text-[#1a3a1a] leading-tight">
                        {{ $item['title'] ?? 'কোয়ালিটি যাচাই' }}
                    </h3>
                </div>
                @endif
            @endforeach

        </div>
    </div>
</section>
    {{-- ══ SECTION 8: REVIEW SLIDER ══ --}}
    <section class="py-14 px-4 md:px-[14%] bg-gray-50 overflow-hidden">
        <div class="container mx-auto">
            <h2 class="text-2xl sm:text-3xl md:text-5xl font-black text-center mb-12 text-[#0D2601]">
                What our customers say about us
            </h2>

            <div class="relative px-4 md:px-10">
                <!-- Swiper Container -->
                <div class="swiper rv-swiper">
                    <div class="swiper-wrapper">
                        @if ($landing->img_paths && count($landing->img_paths) > 0)
                            @foreach ($landing->img_paths as $img)
                                <div class="swiper-slide">
                                    <div class="bg-white rounded-2xl border border-gray-50 overflow-hidden mx-1 mb-10">
                                        <img src="{{ asset('storage/' . $img) }}" alt="slider"
                                            class="w-full h-auto object-cover" alt="Customer Review">
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="swiper-pagination !-bottom-2"></div>
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

    {{-- ══ FOOTER (Exact Image Match) ══ --}}
    <footer class="bg-[#F8F9FA] pt-12 pb-8 px-4 border-t border-gray-200">
        <div class="max-w-7xl mx-auto">

            <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-8">

                <div class="flex items-center gap-3 text-gray-800">
                    <i class="fa-solid fa-location-dot text-xl text-gray-700"></i>
                    <span class="text-sm md:text-base font-medium">
                        {{ $setup->corporate_address ?? 'Kuril, Vatara, Dhaka-1229, Bangladesh' }}
                    </span>
                </div>

                <div class="flex gap-8 text-gray-800 font-medium text-sm md:text-base">
                    <a href="#" class="hover:text-black transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-black transition-colors">Terms & Conditions</a>
                </div>
            </div>

            <hr class="border-gray-300 mb-8">

            <div class="text-center text-gray-800 text-sm md:text-base tracking-wide">
                <p>
                    © {{ date('Y') }} {{ $setup->shop_name ?? '' }}. All rights reserved.
                </p>
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
                    centeredSlides: false,
                    autoplay: {
                        delay: 3000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    breakpoints: {
                        768: {
                            slidesPerView: 2,
                            spaceBetween: 30,
                        },
                        1024: {
                            slidesPerView: 3,
                            spaceBetween: 30,
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>
