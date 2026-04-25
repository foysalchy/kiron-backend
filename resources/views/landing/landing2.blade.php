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
