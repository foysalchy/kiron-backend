<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $landing->title }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            overflow-x: hidden;
        }

        /* Fix: prevent horizontal scroll on mobile */
        .rv-track-wrap {
            overflow: hidden;
        }
    </style>
</head>

<body class="text-gray-800 bg-gray-100 overflow-x-hidden">

    {{-- ══ SECTION 1: HERO ══ --}}
    <section class="bg-[#0D2601] text-white pt-10 pb-16 px-4 text-center">
        <div class="max-w-7xl mx-auto">

            {{-- <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/logo.png" alt="Moringa Logo"
                class="mx-auto mb-6 w-28 md:w-40 lg:w-48"> --}}
            <div>
                <h1 class="text-xl sm:text-2xl md:text-4xl font-semibold leading-snug mb-4">{{ $landing->name }}</h1>
            </div>

            <div class="border border-[#2e8c03] p-4 md:p-6 rounded mb-6">
                <h1 class="text-xl sm:text-2xl md:text-4xl lg:text-5xl font-semibold leading-snug">
                    {!! $landing->short_description !!}
                </h1>
            </div>

            {{-- <p class="text-base sm:text-lg md:text-2xl text-green-50 mb-6">
                525 grams of premium sajina powder + 100 grams of black cumin honey free.
            </p> --}}

            <a href="#order"
                class="inline-flex items-center gap-2 bg-[#f5a623] mb-6 text-white px-6 md:px-10 py-3 md:py-4 border-2 border-[#ad7419] rounded-xl font-bold text-lg sm:text-xl md:text-3xl shadow-lg hover:scale-105 transition-transform">
                Click to order.
                <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/hand.png"
                    class="w-8 md:w-12" alt="">
            </a>

            {{-- Video --}}
            <div class="w-full max-w-6xl mx-auto mt-6">
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
        </div>
    </section>


    {{-- ══ SECTION 2: PROMO PRICE ══ --}}
    <section class="py-16 md:py-24 px-4">
        <div class="max-w-7xl mx-auto relative">
            <div class="bg-[#2e8c03] p-6 md:p-16 rounded-2xl shadow-2xl text-center">
                <div class="text-2xl md:text-4xl text-white leading-tight">
                    {!! $landing->pricing !!}
                </div>
            </div>

            {{-- Overlapping CTA --}}
            <div class="absolute left-1/2 -translate-x-1/2 -bottom-8 md:-bottom-10 w-full flex justify-center px-4">
                <a href="#order"
                    class="inline-flex items-center gap-3 bg-[#f1a32a] text-white px-6 sm:px-10 md:px-16 py-3 md:py-4 rounded-xl font-bold text-lg sm:text-2xl md:text-4xl border-2 border-[#b87d21] whitespace-nowrap shadow-xl">
                    Order Now
                    <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/hand.png"
                        class="w-8 md:w-14" alt="">
                </a>
            </div>
        </div>
    </section>


    {{-- ══ SECTION 3: BENEFITS ══ --}}
    <section class="bg-[#f1fcf1] py-16 px-4 mt-8 md:mt-10">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">

            <div class="bg-white rounded-xl overflow-hidden">
                <div class="benefits-container text-base md:text-xl p-4 md:p-6">
                    <article class="prose prose-slate max-w-none">
                        {!! $landing->extras['content'] ?? '' !!}
                    </article>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden">
                <div class="benefits-container text-base md:text-xl p-4 md:p-6">
                    <article class="prose prose-slate max-w-none">
                        {!! $landing->extras['content2'] ?? '' !!}
                    </article>
                </div>

            </div>
        </div>

        <div class="text-center mt-12">
            <a href="#order"
                class="inline-flex items-center gap-3 bg-[#f1a32a] text-white px-6 sm:px-10 md:px-16 py-3 md:py-5 rounded-xl font-bold text-lg sm:text-2xl md:text-4xl border-2 border-[#b87d21]">
                Order Now
                <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/hand.png"
                    class="w-8 md:w-14" alt="">
            </a>
        </div>
    </section>


    {{-- ══ SECTION 4: PRODUCT BANNER ══ --}}
    <section class="py-12 px-4">
        <div class="max-w-2xl mx-auto text-center">
            <article class="prose prose-slate max-w-none">
                {!! $landing->description ?? '' !!}
            </article>
        </div>
    </section>


    {{-- ══ SECTION 5: WHY TRUST US ══ --}}
    <section class="bg-[#1a3a1a] text-white py-16 px-4">
        <div class="max-w-7xl mx-auto text-center">
            <div class="space-y-10">
                @if (isset($landing->extras['features2']) && is_array($landing->extras['features2']))
                    @foreach ($landing->extras['features2'] as $feature)
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 class="text-2xl font-bold text-[#1a3a1a] mb-4">
                                {{ $feature['title'] ?? '' }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                                <article class="prose prose-slate max-w-none text-lg">
                                    {!! $feature['description'] ?? '' !!}
                                </article>

                                @if (!empty($feature['image']))
                                    <div class="rounded-lg overflow-hidden ">
                                        <img src="{{ asset('storage/' . $feature['image']) }}"
                                            alt="{{ $feature['title'] }}"
                                            class="w-full h-auto object-cover transform hover:scale-105 transition-transform duration-500">

                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="mt-10">
                <a href="#order"
                    class="inline-flex items-center gap-2 bg-[#f5a623] text-white px-6 sm:px-10 md:px-14 py-3 rounded-xl font-semibold text-lg sm:text-2xl md:text-4xl border-2 border-[#b87d21]">
                    Click to order.
                    <img src="{{ asset('./images/landing/img/hand.png') }}" class="w-8 md:w-14" alt="">
                </a>
            </div>
        </div>
    </section>


    {{-- ══ SECTION 6: 8-GRID BENEFITS ══ --}}
    <section class="py-14 px-4 bg-white">
        <div class="max-w-7xl mx-auto text-center">
            <div
                class="inline-block border-4 md:border-10 border-[#4caf50] px-4 py-4 rounded-lg text-base sm:text-lg md:text-2xl font-semibold mb-10 uppercase">
    {{ data_get($landing->extras, 'features_title', 'Why would you eat sajina leaf powder?') }}
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 text-sm sm:text-base md:text-xl">
                @php
                    $grid = $landing->extras['features'] ?? [
                        'It works against serious diseases like diabetes by controlling sugar levels.',
                        'Eating sajan leaves regularly increases the taste of the mouth.',
                        'Helps keep the liver and kidneys healthy.',
                        'High blood pressure will be under control.',
                        'The body does not show signs of age easily.',
                        'Increases immunity.',
                        'It will be very helpful for weight loss.',
                        'Relieves problems caused by fever, cough and cold.',
                    ];
                @endphp

                @foreach ($grid as $g)
                    <div
                        class="border-2 border-[#4caf50] p-3 md:p-4 rounded-lg flex items-center justify-center min-h-[90px] md:min-h-[120px] text-center text-[#1a3a1a] hover:bg-[#e8f5e9] transition-all duration-300">
                        {{ is_array($g) ? $g['content'] ?? '' : $g }}
                    </div>
                @endforeach
            </div>

            <div class="mt-10">
                <a href="#order"
                    class="inline-flex items-center gap-2 bg-[#f5a623] text-white px-6 sm:px-10 md:px-14 py-3 rounded-xl font-semibold text-lg sm:text-2xl md:text-4xl border-2 border-[#b87d21] hover:scale-105 transition-transform">
                    Click to order.
                    <img src="{{ asset('./images/landing/img/hand.png') }}" class="w-8 md:w-14" alt="">
                </a>
            </div>
        </div>
    </section>

    {{-- ══ SECTION 7: PRICING ══ --}}
    <section class="py-14 px-4">
        <div
            class="max-w-7xl mx-auto border-4 md:border-6 border-dashed border-[#2e8c03] rounded-3xl p-6 md:p-14 text-center bg-white shadow-sm">
            <h2 class="text-2xl sm:text-3xl md:text-5xl font-semibold text-[#2e8c03] mb-2">
                Maringa Leaf Powder Price
            </h2>
            <p class="text-base md:text-2xl text-[#2e8c03]/70 mb-8">Affordable Price for the Best Product</p>
            <div class="bg-[#FF0000] text-white p-6 md:p-10 rounded-xl shadow-lg">
                <p class="text-base md:text-2xl font-medium mb-3 opacity-95">
                    500g Maringa Leaf Powder Original Price: BDT 1250
                </p>
                <h3 class="text-xl sm:text-2xl md:text-4xl font-semibold leading-snug">
                    1050 BDT Package with 850 BDT Offer for Limited Time
                </h3>
            </div>
        </div>
    </section>


    {{-- ══ SECTION 8: REVIEW SLIDER ══ --}}
    <section class="py-14 px-4 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <h3 class="text-2xl sm:text-3xl md:text-5xl font-black text-center mb-10 text-[#1a3a1a]">
                What our customers say about us
            </h3>

            <div class="relative rv-track-wrap">
                <div id="rv-track" class="flex gap-4 md:gap-6 transition-transform duration-500 ease-in-out">
                    @php
                        $reviewImages = [
                            'customer-review1.png',
                            'customer-review2.png',
                            'customer-review3.png',
                            'customer-review1.png',
                            'customer-review2.png',
                        ];
                    @endphp
                    @foreach ($reviewImages as $img)
                        <div class="rv-slide flex-shrink-0 bg-white p-2 rounded-xl shadow-lg border border-gray-100">
                            <img src="{{ asset('images/landing/img/' . $img) }}"
                                class="w-full h-auto rounded-xl object-cover" alt="Customer Review">
                        </div>
                    @endforeach
                </div>

                <button onclick="rv(-1)"
                    class="absolute -left-3 md:-left-5 top-1/2 -translate-y-1/2 w-9 h-9 md:w-11 md:h-11 bg-white border border-gray-200 rounded-full shadow-lg flex items-center justify-center z-10">
                    <i class="fas fa-chevron-left text-[#1a3a1a] text-sm"></i>
                </button>
                <button onclick="rv(1)"
                    class="absolute -right-3 md:-right-5 top-1/2 -translate-y-1/2 w-9 h-9 md:w-11 md:h-11 bg-white border border-gray-200 rounded-full shadow-lg flex items-center justify-center z-10">
                    <i class="fas fa-chevron-right text-[#1a3a1a] text-sm"></i>
                </button>
            </div>

            <div id="rv-dots" class="flex justify-center gap-2 mt-8"></div>
        </div>
    </section>


    {{-- ══ SECTION 9: ORDER FORM ══ --}}
    <section id="order" class="py-14 px-4 bg-[#f8faff]">
        <x-landing.order-form />
    </section>


    {{-- ══ FOOTER ══ --}}
    <footer class="bg-gray-50 pt-12 pb-8 px-4 border-t border-gray-100">
        <div class="max-w-5xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-8">
                <div class="flex items-center gap-3 text-gray-700">
                    <div
                        class="w-10 h-10 rounded-full bg-[#2e8c03]/10 flex items-center justify-center text-[#2e8c03]">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <span class="font-medium text-sm md:text-base">{{ $setup->corporate_address }}</span>
                </div>
                <div class="flex gap-6 font-semibold text-gray-600 text-sm md:text-base">
                    <a href="#" class="hover:text-[#2e8c03] transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-[#2e8c03] transition-colors">Terms & Conditions</a>
                </div>
            </div>
            <div class="border-t border-gray-200 pt-6 text-center">
                <p class="text-gray-500 text-sm">
                <p>© {{ date('Y') }} {{ $landing->title ?? '' }}. All rights reserved.</p>
                </p>
            </div>
        </div>
    </footer>

    <script>
        // ── Review Slider ──
        ! function() {
            const track = document.getElementById('rv-track');
            const dots = document.getElementById('rv-dots');
            const slides = track.querySelectorAll('.rv-slide');
            const n = slides.length;
            let cur = 0;

            function perView() {
                return window.innerWidth >= 768 ? 3 : 1;
            }

            function slideWidth() {
                const gap = window.innerWidth >= 768 ? 24 : 16;
                return slides[0].offsetWidth + gap;
            }

            // Set slide widths responsively
            function setSizes() {
                const pv = perView();
                const gap = pv > 1 ? 24 : 16;
                const w = (track.parentElement.clientWidth - gap * (pv - 1)) / pv;
                slides.forEach(s => s.style.width = w + 'px');
            }

            // Build dots
            function buildDots() {
                dots.innerHTML = '';
                const maxI = n - perView();
                for (let i = 0; i <= maxI; i++) {
                    const d = document.createElement('button');
                    d.onclick = () => go(i);
                    dots.appendChild(d);
                }
                updateDots();
            }

            function updateDots() {
                dots.querySelectorAll('button').forEach((d, j) =>
                    d.className = 'h-2.5 rounded-full transition-all ' + (j === cur ? 'bg-[#1a3a1a] w-8' :
                        'bg-gray-300 w-2.5')
                );
            }

            function go(i) {
                const maxI = n - perView();
                cur = Math.max(0, Math.min(i, maxI));
                // loop back
                if (i > maxI) cur = 0;
                if (i < 0) cur = maxI;
                track.style.transform = `translateX(-${cur * slideWidth()}px)`;
                updateDots();
            }

            window.rv = dir => go(cur + dir);

            let t = setInterval(() => rv(1), 4000);
            track.parentElement.addEventListener('mouseenter', () => clearInterval(t));
            track.parentElement.addEventListener('mouseleave', () => t = setInterval(() => rv(1), 4000));

            // Touch swipe
            let sx = 0;
            track.addEventListener('touchstart', e => sx = e.touches[0].clientX, {
                passive: true
            });
            track.addEventListener('touchend', e => {
                if (Math.abs(sx - e.changedTouches[0].clientX) > 40) rv(sx > e.changedTouches[0].clientX ? 1 : -1);
            });

            window.addEventListener('resize', () => {
                setSizes();
                buildDots();
                go(0);
            });

            setSizes();
            buildDots();
        }();

        // ── Order qty ──
        const UNIT_PRICE = 999,
            DELIVERY = 100;
        let qty = 1;

        function changeQty(delta) {
            qty = Math.max(1, qty + delta);
            const sub = qty * UNIT_PRICE,
                total = sub + DELIVERY;
            document.getElementById('qty-display').innerText = qty;
            document.getElementById('unit-price-display').innerText = sub.toFixed(2) + '৳';
            document.getElementById('summary-qty').innerText = qty;
            document.getElementById('summary-subtotal').innerText = sub.toFixed(2);
            document.getElementById('summary-total').innerText = total.toLocaleString('en-BD', {
                minimumFractionDigits: 2
            }) + '৳';
            document.getElementById('btn-total').innerText = total.toLocaleString('en-BD', {
                minimumFractionDigits: 2
            });
        }

        function submitOrder() {
            const n = document.getElementById('f-name').value.trim();
            const p = document.getElementById('f-phone').value.trim();
            const a = document.getElementById('f-address').value.trim();
            if (!n) return alert('Write Your name');
            if (!p) return alert('Write your phone number');
            if (!a) return alert('Write your address');
            alert('Order placed successfully! We will contact you shortly.');
        }
    </script>

</body>

</html>
