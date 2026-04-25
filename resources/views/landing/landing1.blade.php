<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moringa - Premium Sajina Leaf Powder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-['Outfit'] text-gray-800 bg-gray-100">

    {{-- ══ SECTION 1: HERO ══ --}}
    <section class="bg-[#0D2601] text-white pt-12 pb-20 px-4 text-center">
        <div class="max-w-7xl mx-auto">

            <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/logo.png" alt="Moringa Logo"
                class="mx-auto mb-8 w-51">

            <div class="border-1 border-[#2e8c03] p-6 rounded mb-6">
                <h1 class="text-2xl md:text-4xl lg:text-5xl font-semibold leading-tight">
                    1 glass of sajan leaf juice daily will benefit you and your family
                    <span class="text-[#d97f11]">300 diseases</span>
                    Which will protect you from research-tested!!
                </h1>
            </div>

            <p class="text-lg md:text-3xl text-green-50 mb-8">
                525 grams of premium sajina powder + 100 grams of black cumin honey free.
            </p>

            <a href="#order"
                class="inline-flex items-center gap-2 bg-[#f5a623] mb-6 text-white px-8 py-4 border-3 border-[#ad7419] rounded-xl font-bold text-xl md:text-3xl shadow-lg hover:scale-105 transition-transform">
                Click to order.
                <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/hand.png" class="w-12"
                    alt="">
            </a>

            <!-- Video Section Start -->
            <div class="max-w-6xl mx-auto">
                <div
                    class="relative border-[12px] md:border-[22px] border-[#35B11E] rounded-lg md:rounded-3xl overflow-hidden bg-black shadow-2xl">
                    <div class="relative aspect-video">

                        {{-- ── YouTube Iframe ── --}}
                        <iframe
                            src="https://www.youtube.com/embed/uFjU5zFJx3E?autoplay=0&mute=0&controls=0&playsinline=1&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1&enablejsapi=1"
                            frameborder="0" allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" title="Funnel Liner Logo Launching Video"
                            class="absolute inset-0 w-full h-full">
                        </iframe>

                        {{-- ── Overlay: Top title + Bottom icons ── --}}
                        {{-- pointer-events-none so clicks go to iframe --}}
                        <div
                            class="absolute inset-0 pointer-events-none flex flex-col justify-between p-3 md:p-6 bg-gradient-to-t from-black/70 via-transparent to-black/40">

                            {{-- Top Title --}}
                            <div class="flex items-center gap-3">

                            </div>

                            {{-- Bottom Icons — always visible, never hides --}}
                            <div class="flex justify-between items-end">

                                {{-- Left: Share --}}
                                <div>

                                </div>

                                {{-- Right: More videos + YouTube --}}
                                <div
                                    class="flex items-center gap-3 md:gap-4 bg-black/40 backdrop-blur-sm rounded-full px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-white text-[10px] md:text-xs font-medium">More videos</span>
                                        <div
                                            class="w-8 h-6 bg-white/20 rounded border border-white/30 overflow-hidden flex-shrink-0">
                                            <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/logo.png"
                                                class="w-full h-full object-cover" alt="">
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-brands fa-youtube text-red-600 text-xl md:text-2xl"></i>
                                        <span class="text-white font-bold text-sm md:text-base">YouTube</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- Video Section End -->
        </div>
    </section>


    {{-- ══ SECTION 2: PROMO PRICE ══ --}}
    <section class="py-24 px-4">
        <div class="max-w-7xl mx-auto relative">

            <!-- Main Green Container -->
            <div class="bg-[#2e8c03] p-8 md:p-24 rounded-2xl shadow-2xl text-center">

                <!-- Red Header Banner -->
                <div
                    class="bg-[#FF0034] text-white py-6 text-xl md:text-4xl font-medium rounded-xl mb-12 shadow-lg w-full leading-tight">
                    Limited Time Offer: Get 30% Off on Premium Sajina Leaf Powder!
                </div>

                <!-- White Price Box -->
                <div class="bg-white rounded-xl overflow-hidden shadow-inner border border-white">
                    <!-- Regular Price -->
                    <div class="py-6 px-8">
                        <p class="text-[#2e8c03] line-through text-2xl md:text-4xl font-medium decoration-4">
                            powder price of 500 grams of Moringa: 1250/=
                        </p>
                    </div>

                    <!-- Dotted Divider (Matches the image) -->
                    <div class="border-t-3 border-dotted border-[#2e8c03] mx-6"></div>

                    <!-- Offer Price -->
                    <div class="py-10 px-4">
                        <p class="text-[#2e8c03] font-medium text-2xl md:text-4xl">
                            The package of 1050 taka is 850 taka, the offer is for a limited time.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Overlapping 3D CTA Button -->
            <div class="absolute left-1/2 -translate-x-1/2 -bottom-10 w-full flex justify-center">
                <a href="#order"
                    class="inline-flex items-center gap-4 bg-[#f1a32a] text-white px-10 md:px-20 py-4 rounded-xl font-bold text-2xl md:text-4xl border-3 border-[#b87d21] whitespace-nowrap">
                    Order Now
                    <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/hand.png"
                        class="w-12 md:w-16" alt="hand icon">
                </a>
            </div>
        </div>
    </section>

    {{-- ══ SECTION 3: BENEFITS & RULES ══ --}}
    <section class="bg-[#f1fcf1] py-16 px-4">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10">

            <!-- Left Box: Benefits -->
            <div class="bg-white rounded-3xl overflow-hidden shadow-sm">
                <div class="w-full border-12 border-[#2e8c03] rounded-3xl py-5 bg-white shadow-sm">
                    <h3 class="text-2xl md:text-3xl font-extrabold text-center text-[#1a3a1a]">
                        Benefits of Sajina Leaf Powder
                    </h3>
                </div>

                <!-- List Items -->
                <ul class="divide-y divide-gray-100 text-xl">
                    <li class="flex items-start gap-4 p-5 hover:bg-gray-50 transition-colors">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="High Blood Pressure Control"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a] leading-relaxed">High blood pressure control.</span>
                    </li>
                    <li class="flex items-start gap-4 p-5 hover:bg-gray-50 transition-colors">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Anti-Bacterial Properties"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a]  leading-relaxed">It has anti-bacterial properties. It
                            helps keep the liver and kidneys healthy and also enhances skin beauty.</span>
                    </li>
                    <li class="flex items-start gap-4 p-5 hover:bg-gray-50 transition-colors">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Reduces Cholesterol"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a] leading-relaxed">Reduces cholesterol in the
                            blood.</span>
                    </li>
                    <li class="flex items-start gap-4 p-5 hover:bg-gray-50 transition-colors">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Controls Acidity"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a] leading-relaxed">Controls acidity or gastritis.</span>
                    </li>
                    <li class="flex items-start gap-4 p-5 hover:bg-gray-50 transition-colors">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Helps Control Sugar Levels"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a] leading-relaxed">Helps control sugar levels in the body,
                            thus protecting against diabetes and other serious diseases.</span>
                    </li>
                    <li class="flex items-start gap-4 p-5">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Helps with Respiratory Issues"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a] leading-relaxed">Helps with respiratory issues.</span>
                    </li>
                </ul>
            </div>

            <!-- Right Box: Consumption Rules -->
            <div class="bg-white rounded-3xl overflow-hidden shadow-sm">
                <!-- Header Title Box -->
                <div class="w-full border-12 border-[#2e8c03] rounded-3xl py-5 bg-white shadow-sm">
                    <h3 class="text-2xl md:text-3xl font-extrabold text-center text-[#1a3a1a]">
                        How to consume Sajina Leaf Powder
                    </h3>
                </div>


                <!-- List Items -->
                <ul class="divide-y divide-gray-100 text-xl">
                    <li class="flex items-start gap-4 p-5 hover:bg-gray-50 transition-colors">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Consume on an empty stomach"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a]  leading-relaxed">Consume on an empty stomach with a
                            glass of water, mixing 2 teaspoons of Sajina Leaf Powder.</span>
                    </li>
                    <li class="flex items-start gap-4 p-5 hover:bg-gray-50 transition-colors">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Mix with honey"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a]  leading-relaxed">Mix with honey for consumption.</span>
                    </li>
                    <li class="flex items-start gap-4 p-5 hover:bg-gray-50 transition-colors">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Mix with milk"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a]  leading-relaxed">Mix with milk for consumption.</span>
                    </li>
                    <li class="flex items-start gap-4 p-5 hover:bg-gray-50 transition-colors">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Mix with lentils"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a]  leading-relaxed">Mix with lentils for
                            consumption.</span>
                    </li>
                    <li class="flex items-start gap-4 p-5 hover:bg-gray-50 transition-colors">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Mix with pears"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a]  leading-relaxed">Mix with pears for consumption.</span>
                    </li>
                    <li class="flex items-start gap-4 p-5">
                        <img src="{{ asset('./images/landing/img/arrow.png') }}" alt="Beneficial for skin health"
                            class="w-10 h-10 mt-1">
                        <span class="text-[#1a3a1a]  leading-relaxed">Beneficial for skin health.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- CTA Button (Same style as previous section) -->
        <div class="text-center mt-14">
            <a href="#order"
                class="inline-flex items-center gap-4 bg-[#f1a32a] text-white px-10 md:px-20 py-4 md:py-5 rounded-xl font-bold text-2xl md:text-4xl  transition-transform border-3 border-[#b87d21]">
                Order Now
                <img src="https://landing-page-images-1.s3.ap-south-1.amazonaws.com/landing-34/hand.png"
                    class="w-10 md:w-14" alt="hand">
            </a>
        </div>
    </section>


    {{-- ══ SECTION 4: PRODUCT INFO & PHONE BANNER ══ --}}
    <section class="py-16 px-4 bg-white">
        <div class="max-w-4xl mx-auto text-center">

            <!-- Top Bengali Text -->
            <p class="text-xl md:text-2xl font-bold leading-relaxed text-[#1a3a1a] mb-12">
                গ্রাম থেকে সংগ্রহ করা শতভাগ ন্যাচারাল সজিনা পাতা <br class="hidden md:block">
                নিজেদের তত্ত্বাবধানে স্বাস্থ্য সম্মত পরিবেশে রোদে শুকিয়ে গুড়া <br class="hidden md:block">
                করা হয়। প্রোডাক্ট হাতে পেয়ে, দেখে, কোয়ালিটি চেক করে <br class="hidden md:block">
                পেমেন্টে করার সুবিধা।
            </p>

            <!-- Main Card Container -->

            <div class="relative rounded-xl overflow-hidden">
                <!-- Background Image (Use your actual product image path here) -->
                <div class="relative">
                    <img src="{{ asset('./images/landing/img/order-baner.png') }}" height="120" width="120"
                        class="w-full h-auto object-cover" alt="Moringa Powder">
                </div>
            </div>
        </div>
    </section>


    {{-- ══ SECTION 5: WHY TRUST US ══ --}}
    <section class="bg-[#1a3a1a] text-white py-20 px-4">
        <div class="container mx-auto text-center">
            <div class="inline-block border-8 border-[#4caf50] px-8 py-4 rounded-lg font-bold mb-12">
                <h2 class="text-4xl Uppercase">Why trust us??</h2>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-10 text-left">
                <div class="flex-1 space-y-5 text-xl">
                    <p class="flex items-start gap-3">
                        <img src="{{ asset('./images/landing/img/arr2.png') }}" alt="Arrow"
                            class="w-10 h-10 mt-1 flex-shrink-0">
                        Premium sajina leaf powder, completely prepared under our own supervision, maintaining 100%
                        hygiene, dust-free.
                    </p>
                    <p class="flex items-start gap-3">
                        <img src="{{ asset('./images/landing/img/arr2.png') }}" alt="Arrow"
                            class="w-10 h-10 mt-1 flex-shrink-0">
                        The convenience of receiving the product, viewing it, checking the quality and making payment.
                    </p>
                    <p class="flex items-start gap-3">
                        <img src="{{ asset('./images/landing/img/arr2.png') }}" alt="Arrow"
                            class="w-10 h-10 mt-1 flex-shrink-0">
                        You will receive home delivery via courier all over Bangladesh.
                    </p>
                    <p class="flex items-start gap-3">
                        <img src="{{ asset('./images/landing/img/arr2.png') }}" alt="Arrow"
                            class="w-10 h-10 mt-1 flex-shrink-0">
                        You can contact us at any time.
                    </p>
                    <p class="flex items-start gap-3">
                        <img src="{{ asset('./images/landing/img/arr2.png') }}" alt="Arrow"
                            class="w-10 h-10 mt-1 flex-shrink-0">
                        You don't have to pay a single penny in advance. You will pay after receiving the product.
                    </p>
                </div>
                <div class="flex-1 w-full">
                    <div class="bg-white p-2 rounded-xl">
                        <img src="{{ asset('./images/landing/img/moringa.png') }}" class="w-full rounded-lg"
                            alt="Product">
                    </div>
                </div>
            </div>

            <div class="mt-12">
                <a href="#order"
                    class="border-3 border-[#b87d21] inline-flex items-center gap-2 bg-[#f5a623] text-white px-8 md:px-12 py-3 rounded-xl font-semibold text-2xl md:text-4xl">
                    Click to order.
                    <img src="{{ asset('./images/landing/img/hand.png') }}" class="w-18" alt="">
                </a>
            </div>
        </div>
    </section>


    {{-- ══ SECTION 6: 8-GRID BENEFITS ══ --}}
    <section class="py-16 px-4 bg-white container mx-auto text-center">

        <div
            class="inline-block border-8 md:border-12 border-[#4caf50] px-4 py-6 rounded-lg text-lg md:text-2xl font-semibold mb-12 uppercase">
            Why would you eat sajina leaf powder when there is so much to eat?
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm md:text-xl">
            <div
                class="border-2 border-[#4caf50] p-4 rounded-lg flex items-center justify-center  min-h-[100px] text-center text-gray-700">
                It works against serious diseases like diabetes by controlling sugar levels in the body.</div>
            <div
                class="border-2 border-[#4caf50] p-4 rounded-lg flex items-center justify-center  min-h-[100px] text-center text-gray-700">
                Eating sajan leaves regularly increases the taste of the mouth.</div>
            <div
                class="border-2 border-[#4caf50] p-4 rounded-lg flex items-center justify-center  min-h-[100px] text-center text-gray-700">
                Helps keep the liver and kidneys healthy.</div>
            <div
                class="border-2 border-[#4caf50] p-4 rounded-lg flex items-center justify-center  min-h-[100px] text-center text-gray-700">
                High blood pressure will be under control.</div>
            <div
                class="border-2 border-[#4caf50] p-4 rounded-lg flex items-center justify-center  min-h-[100px] text-center text-gray-700">
                The body does not show signs of age easily.</div>
            <div
                class="border-2 border-[#4caf50] p-4 rounded-lg flex items-center justify-center  min-h-[100px] text-center text-gray-700">
                Increases immunity.</div>
            <div
                class="border-2 border-[#4caf50] p-4 rounded-lg flex items-center justify-center  min-h-[100px] text-center text-gray-700">
                It will be very helpful for weight loss.</div>
            <div
                class="border-2 border-[#4caf50] p-4 rounded-lg flex items-center justify-center  min-h-[100px] text-center text-gray-700">
                Relieves problems caused by fever, cough and cold.</div>
        </div>

        <div class="mt-12">
            <a href="#order"
                class="border-3 border-[#b87d21] inline-flex items-center gap-2 bg-[#f5a623] text-white px-8 md:px-12 py-3 rounded-xl font-semibold text-2xl md:text-4xl">
                Click to order.
                <img src="{{ asset('./images/landing/img/hand.png') }}" class="w-18" alt="">
            </a>
        </div>

    </section>


    {{-- ══ SECTION 7: PRICING BANNER ══ --}}
    <section class="py-20 px-4">
        <!-- Outer Dashed Border Container -->
        <div
            class="max-w-7xl mx-auto border-[6px] border-dashed border-[#2e8c03] rounded-[30px] p-6 md:p-14 text-center bg-white shadow-sm">

            <!-- Header Titles -->
            <h2 class="text-3xl md:text-5xl font-semibold text-[#2e8c03] mb-3">
                Maringa Leaf Powder Price
            </h2>
            <p class="text-lg md:text-2xl text-[#2e8c03]/70 mb-10">
                Affordable Price for the Best Product
            </p>

            <!-- Solid Red Offer Box -->
            <div class="bg-[#FF0000] text-white p-8 md:p-10 rounded-xl shadow-[0_20px_40px_rgba(255,0,0,0.25)]">
                <p class="text-xl md:text-2xl font-medium mb-4 opacity-95">
                    500g Maringa Leaf Powder Original Price: BDT 1250
                </p>
                <h3 class="text-2xl md:text-5xl font-semibold leading-tight md:leading-snug">
                    1050 BDT Package with 850 BDT Offer for Limited Time
                </h3>
            </div>

        </div>
    </section>


    {{-- ══ SECTION 8: IMAGE REVIEWS SLIDER ══ --}}
    <section class="py-16 px-4 bg-gray-50">
        <div class="max-w-6xl mx-auto">

            <h3 class="text-3xl md:text-5xl font-black text-center mb-12 text-[#1a3a1a]">
                What our customers say about us
            </h3>

            <div class="relative">
                {{-- Track — native scroll, no JS positioning needed --}}
                <div id="rv-track" class="flex gap-6 overflow-x-hidden scroll-smooth">

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
                        <div
                            class="rv-slide flex-shrink-0 w-full md:w-[calc(33.333%-16px)] bg-white p-2 rounded-2xl shadow-lg border border-gray-100">
                            <img src="{{ asset('images/landing/img/' . $img) }}"
                                class="w-full h-auto rounded-xl object-cover" alt="Customer Review">
                        </div>
                    @endforeach

                </div>

                {{-- Arrows --}}
                <button onclick="rv(-1)"
                    class="absolute -left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 z-10">
                    <i class="fas fa-chevron-left text-[#1a3a1a] text-sm"></i>
                </button>
                <button onclick="rv(1)"
                    class="absolute -right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 z-10">
                    <i class="fas fa-chevron-right text-[#1a3a1a] text-sm"></i>
                </button>
            </div>

            {{-- Dots --}}
            <div id="rv-dots" class="flex justify-center gap-2 mt-8"></div>
        </div>
    </section>


    {{-- ══ SECTION 9: ORDER FORM ══ --}}
    <section id="order" class="py-16 px-4 bg-[#f8faff]">
        <x-landing.order-form />
    </section>

    {{-- ══ FOOTER ══ --}}
    <footer class="bg-gray-50 pt-16 pb-10 px-4 border-t border-gray-100">
        <div class="max-w-6xl mx-auto">
            <!-- Top Section: Info and Links -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-10">

                <!-- Location Info -->
                <div class="flex items-center gap-3 text-gray-700 group">
                    <div
                        class="w-10 h-10 rounded-full bg-[#2e8c03]/10 flex items-center justify-center text-[#2e8c03] group-hover:bg-[#2e8c03] group-hover:text-white transition-all">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <span class="font-medium text-base">Kuril, Vatara, Dhaka-1229, Bangladesh</span>
                </div>

                <!-- Policy Links -->
                <div class="flex gap-8 font-semibold text-gray-600">
                    <a href="#"
                        class="hover:text-[#2e8c03] transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-[#2e8c03] hover:after:w-full after:transition-all">
                        Privacy Policy
                    </a>
                    <a href="#"
                        class="hover:text-[#2e8c03] transition-colors relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-[#2e8c03] hover:after:w-full after:transition-all">
                        Terms & Conditions
                    </a>
                </div>
            </div>

            <!-- Horizontal Divider (Dashed/Dotted style as per original design) -->
            <div class="border-t-1 border-gray-200 w-full mb-8"></div>

            <!-- Bottom Section: Copyright -->
            <div class="text-center">
                <p class="text-gray-500 text-sm tracking-wide">
                    © 2026 All Rights Reserved Designed by
                    <span class="text-[#2e8c03] font-black uppercase ml-1">Funnel Liner</span>
                </p>
            </div>
        </div>
    </footer>

    <script>
        function updateTotal(amount) {
            document.getElementById('total-amount').innerText = amount.toFixed(2);
            document.getElementById('btn-total').innerText = amount.toFixed(2);
        }

        function placeOrder() {
            const name = document.getElementById('f-name').value.trim();
            const phone = document.getElementById('f-phone').value.trim();
            const address = document.getElementById('f-address').value.trim();

            if (!name) {
                alert('Please enter your name.');
                return;
            }
            if (!phone) {
                alert('Please enter your phone number.');
                return;
            }
            if (!address) {
                alert('Please enter your address.');
                return;
            }

            alert('Order placed successfully! We will contact you shortly.');
        }
    </script>
    <script>
        const UNIT_PRICE = 999;
        const DELIVERY = 100;
        let qty = 1;

        function changeQty(delta) {
            qty = Math.max(1, qty + delta);
            const subtotal = qty * UNIT_PRICE;
            const total = subtotal + DELIVERY;

            document.getElementById('qty-display').innerText = qty;
            document.getElementById('unit-price-display').innerText = subtotal.toFixed(2) + '৳';
            document.getElementById('summary-qty').innerText = qty;
            document.getElementById('summary-subtotal').innerText = subtotal.toFixed(2);
            document.getElementById('summary-total').innerText = total.toLocaleString('en-BD', {
                minimumFractionDigits: 2
            }) + '৳';
            document.getElementById('btn-total').innerText = total.toLocaleString('en-BD', {
                minimumFractionDigits: 2
            });
        }

        function submitOrder() {
            const name = document.getElementById('f-name').value.trim();
            const phone = document.getElementById('f-phone').value.trim();
            const address = document.getElementById('f-address').value.trim();

            if (!name) {
                alert('Write Your name');
                return;
            }
            if (!phone) {
                alert('Write your phone number');
                return;
            }
            if (!address) {
                alert('Write your address');
                return;
            }

            alert('Order placed successfully! We will contact you shortly.');
        }
    </script>
    <script>
        ! function() {
            const track = document.getElementById('rv-track');
            const dots = document.getElementById('rv-dots');
            const slides = track.querySelectorAll('.rv-slide');
            const n = slides.length;
            let cur = 0;

            // build dots
            slides.forEach((_, i) => {
                const d = document.createElement('button');
                dots.appendChild(d);
                d.onclick = () => go(i);
            });

            function go(i) {
                cur = (i + n) % n; // wrap
                track.scrollLeft = slides[cur].offsetLeft - track.offsetLeft;
                dots.querySelectorAll('button').forEach((d, j) =>
                    d.className = 'h-2.5 rounded-full transition-all ' + (j === cur ? 'bg-[#1a3a1a] w-8' :
                        'bg-gray-300 w-2.5')
                );
            }

            window.rv = dir => go(cur + dir);

            // auto-play
            let t = setInterval(() => rv(1), 4000);
            track.parentElement.addEventListener('mouseenter', () => clearInterval(t));
            track.parentElement.addEventListener('mouseleave', () => t = setInterval(() => rv(1), 4000));

            go(0); // init
        }();
    </script>


</body>

</html>
