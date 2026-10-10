@props(['landing'])
@php
    $mainProduct = $landing->product;
    $insideCharge = $setup->inside_charge ?? 60;
    $outsideCharge = $setup->outside_charge ?? 100;
@endphp

<div class="max-w-7xl mx-auto bg-white p-6 md:p-12 rounded-xl shadow-sm">
    <form action="{{ route('landing.order.store') }}" method="POST" id="landing-order-form">
        {{-- Success & Error Messages --}}
        @if(session('success'))
            <div
                class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-r-lg shadow-sm flex items-center gap-3">
                <svg aria-hidden="true" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16 9"></path>
                </svg>
                <div>
                    <p class="font-bold">সফল হয়েছে!</p>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div
                class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-lg shadow-sm flex items-center gap-3">
                <svg aria-hidden="true" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v4m0 4h.01M10.3 3.9 2.2 18a2 2 0 0 0 1.7 3h16.2a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z">
                    </path>
                </svg>
                <div>
                    <p class="font-bold">দুঃখিত!</p>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif
        @csrf
        <input type="hidden" name="product_id" value="{{ $landing->product_id }}">
        <input type="hidden" name="landing_page_id" value="{{ $landing->id }}">
        <input type="hidden" name="qty" id="input-qty" value="1">

        @if ($mainProduct->type === 'variation')
            <input type="hidden" name="variation_id" id="selected-variation-id">
        @endif

        {{-- Header --}}
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-bold mb-5 text-black">অর্ডার করতে সঠিক তথ্য দিয়ে</h2>
            <div
                class="inline-block bg-[#1f8a54] text-white px-10 py-3 rounded-lg text-xl md:text-2xl shadow-md font-bold">
                নিচের ফর্মটি পূরণ করুন</div>
        </div>

        {{-- Product Selection Area --}}
        <div class="space-y-4 mb-10">
            <label class="block text-lg font-bold mb-4 text-gray-800">পণ্যটি সিলেক্ট করুন <span
                    class="text-red-500">*</span></label>

            @if ($mainProduct->type === 'variation')
                @foreach ($mainProduct->variations as $index => $variation)
                    @php
                        $vSalePrice = $variation->regular_price - $variation->discount;
                        $vImage = $variation->image_url ?? $mainProduct->thumbnail_url ?? asset('images/template1/frontend/default.webp');
                    @endphp
                    <label class="relative cursor-pointer block">
                        <input type="radio" name="variant_radio" value="{{ $variation->id }}" {{ $index === 0 ? 'checked' : '' }}
                            onchange="updateVariation('{{ $variation->id }}', {{ $vSalePrice }}, '{{ $variation->display_name }}', '{{ $vImage }}')"
                            class="hidden peer">

                        <div
                            class="flex flex-col md:flex-row items-center justify-between gap-4 p-4 border-2 border-gray-200 rounded-xl transition-all peer-checked:border-[#1f8a54] peer-checked:bg-green-50 bg-white hover:border-gray-300">
                            <div class="flex items-center gap-4 w-full">
                                <div class="w-16 h-16 bg-white rounded-lg overflow-hidden flex-shrink-0 border border-gray-100">
                                    <img src="{{ $vImage }}" width="64" height="64" class="w-full h-full object-contain"
                                        alt="PTOFUVT">
                                </div>
                                <div class="flex-grow text-left">
                                    <p class="text-sm md:text-md font-bold text-gray-800">{{ $mainProduct->title }}</p>
                                    <p class="text-xs text-[#145a32] font-bold uppercase mt-1">Option:
                                        {{ $variation->display_name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 flex-shrink-0">
                                <span
                                    class="text-lg font-bold text-gray-900">{{ number_format($vSalePrice, 2) }}{{ $setup->currency }}</span>
                            </div>
                        </div>
                    </label>
                @endforeach
            @else
                <div
                    class="border-2 border-[#1f8a54] bg-green-50 rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4 w-full">
                        <div class="w-16 h-16 bg-white rounded-lg overflow-hidden flex-shrink-0 border border-gray-100">
                            <img src="{{ $landing->product->thumbnail_url ?? asset('images/default.webp') }}" width="64"
                                height="64" class="w-full h-full object-contain" alt="THUMBNAIL">
                        </div>
                        <div class="text-left">
                            <p class="text-md font-bold text-gray-800">{{ $landing->product->title }}</p>
                        </div>
                    </div>
                    <span
                        class="text-lg font-bold text-gray-900">{{ number_format($mainProduct->sale_price, 2) }}{{ $setup->currency }}</span>
                </div>
            @endif
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

            {{-- LEFT: Billing Info --}}
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <label for="name" class="block text-md font-bold mb-2 text-gray-800">আপনার নাম লিখুন <span
                            class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" required
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 bg-white focus:border-[#1f8a54] outline-none">
                </div>
                <div>
                    <label for="phone" class="block text-md font-bold mb-2 text-gray-800">মোবাইল নাম্বার লিখুন <span
                            class="text-red-500">*</span></label>
                    <input type="tel" id="phone" name="phone" required
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 bg-white focus:border-[#1f8a54] outline-none">
                </div>
                <div>
                    <label for="address" class="block text-md font-bold mb-2 text-gray-800">সম্পূর্ণ ঠিকানা লিখুন <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="address" name="address" required
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 bg-white focus:border-[#1f8a54] outline-none">
                </div>
            </div>

            {{-- RIGHT: Order Summary with Shipping Area --}}
            <div class="lg:col-span-5">
                <div class="bg-white p-2 sticky top-8 ">

                    {{-- Table Header --}}
                    <div
                        class="flex justify-between text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 pb-3 border-b border-dashed border-gray-200">
                        <span>Product</span><span class="text-right">Price</span>
                    </div>

                    {{-- Product Info Row --}}
                    <div
                        class="flex justify-between items-start gap-4 pb-5 mb-5 border-b border-dashed border-gray-200">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-14 h-14 rounded-lg overflow-hidden flex-shrink-0 border border-gray-100 bg-gray-50">
                                <img id="summary-img" src="{{ $mainProduct->thumbnail_url }}" width="56" height="56"
                                    alt="{{ $landing->product->title ?? 'Product Image' }}"
                                    class="w-full h-full object-contain">
                            </div>
                            <div class="max-w-[150px]">
                                <p class="text-xs font-bold text-gray-800 leading-tight">{{ $mainProduct->title }}</p>
                                <p id="summary-variant-name"
                                    class="text-[10px] text-green-600 font-bold mt-1 uppercase"></p>
                            </div>
                        </div>
                        <div class="text-right whitespace-nowrap pt-1">
                            <span class="text-sm font-bold text-gray-800">
                                <span class="text-gray-400">×</span> <span id="summary-qty">1</span>
                                <span id="summary-unit-price" class="ml-1"></span>{{ $setup->currency }}
                            </span>
                        </div>
                    </div>

                    {{-- Qty Selector & Shipping Area (Inside Summary) --}}
                    <div class="px-2 space-y-4 mb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-700">Quantity</span>
                            <div class="flex items-center border border-gray-300 rounded bg-white overflow-hidden">
                                <button type="button" onclick="changeQty(-1)"
                                    class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 border-r border-gray-300 font-bold">−</button>
                                <span id="qty-display" class="w-10 text-center font-bold text-sm">1</span>
                                <button type="button" onclick="changeQty(1)"
                                    class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 border-l border-gray-300 font-bold">+</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-500 uppercase tracking-wide">Delivery
                                Area</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="shipping_area" value="inside" checked
                                        onchange="updateDeliveryCharge({{ $insideCharge }})" class="hidden peer">
                                    <div
                                        class="p-2 border-2 border-gray-100 rounded-lg text-center text-sm font-semibold text-gray-600 peer-checked:border-[#1f8a54] peer-checked:text-[#145a32] bg-gray-50">
                                        Inside Dhaka</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="shipping_area" value="outside"
                                        onchange="updateDeliveryCharge({{ $outsideCharge }})" class="hidden peer">
                                    <div
                                        class="p-2 border-2 border-gray-100 rounded-lg text-center text-sm font-semibold text-gray-600 peer-checked:border-[#1f8a54] peer-checked:text-[#145a32] bg-gray-50">
                                        Outside Dhaka</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Calculations --}}
                    <div class="px-2 space-y-2 border-t border-dashed border-gray-100 pt-4 mb-6">
                        <div class="flex justify-between text-sm md:text-base text-gray-800">
                            <span>Subtotal</span>
                            <span class="font-bold text-gray-800"><span
                                    id="summary-subtotal"></span>{{ $setup->currency }}</span>
                        </div>
                        <div class="flex justify-between text-sm md:text-base text-gray-800">
                            <span>Shipping Charge</span>
                            <span class="font-bold text-red-600">+<span
                                    id="delivery-charge-display"></span>{{ $setup->currency }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center text-lg font-bold text-gray-900 pt-2 border-t border-gray-50">
                            <span>Total</span>
                            <span id="summary-total" class="text-2xl font-black"></span>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-[#1f8a54] hover:bg-[#176840] text-white font-bold py-5 rounded-xl text-2xl  active:translate-y-1 transition-all flex items-center justify-center gap-2">
                        অর্ডার করুন <span id="btn-total"></span>{{ $setup->currency }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    let UNIT_PRICE = parseFloat("{{ $mainProduct->type === 'single' ? $mainProduct->sale_price : 0 }}") || 0;
    let DELIVERY = parseFloat("{{ $insideCharge }}");
    const CURRENCY = "{{ $setup->currency }}";
    let qty = 1;
    const isVarProduct = {{ $mainProduct->type === 'variation' ? 'true' : 'false' }};

    document.addEventListener("DOMContentLoaded", function () {
        if (isVarProduct) {
            const firstRadio = document.querySelector('input[name="variant_radio"]:checked');
            if (firstRadio) firstRadio.dispatchEvent(new Event('change'));
        } else {
            updateUI();
        }
    });

    function updateVariation(id, price, name, img) {
        document.getElementById('selected-variation-id').value = id;
        UNIT_PRICE = parseFloat(price);
        document.getElementById('summary-img').src = img;
        const varLabel = document.getElementById('summary-variant-name');
        if (varLabel) varLabel.innerText = "Option: " + name;
        updateUI();
    }

    function updateDeliveryCharge(amount) {
        DELIVERY = parseFloat(amount);
        updateUI();
    }

    function changeQty(delta) {
        qty = Math.max(1, qty + delta);
        updateUI();
    }

    function updateUI() {
        const sub = qty * UNIT_PRICE;
        const total = sub + DELIVERY;

        document.getElementById('qty-display').innerText = qty;
        document.getElementById('input-qty').value = qty;
        document.getElementById('summary-qty').innerText = qty;

        const unitFormatted = UNIT_PRICE.toLocaleString(undefined, { minimumFractionDigits: 2 });
        const subFormatted = sub.toLocaleString(undefined, { minimumFractionDigits: 2 });
        const totalFormatted = total.toLocaleString(undefined, { minimumFractionDigits: 2 });

        document.getElementById('summary-unit-price').innerText = unitFormatted;
        document.getElementById('summary-subtotal').innerText = subFormatted;
        document.getElementById('delivery-charge-display').innerText = DELIVERY.toFixed(2);
        document.getElementById('summary-total').innerText = totalFormatted + CURRENCY;
        document.getElementById('btn-total').innerText = totalFormatted;
    }

    document.getElementById('landing-order-form').onsubmit = function (e) {
        if (isVarProduct && !document.getElementById('selected-variation-id').value) {
            e.preventDefault();
            alert('দয়া করে একটি অপশন সিলেক্ট করুন!');
            return false;
        }
        this.querySelector('button[type="submit"]').disabled = true;
        this.querySelector('button[type="submit"]').innerText = "প্রসেসিং...";
        return true;
    };
    @if(session('success'))
        Swal.fire({
            title: 'অর্ডার সফল হয়েছে!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'ঠিক আছে',
            confirmButtonColor: '#1f8a54'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: 'দুঃখিত!',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonText: 'আবার চেষ্টা করুন',
            confirmButtonColor: '#d33'
        });
    @endif

    // Live preview scroll preservation (SessionStorage method)
    window.addEventListener('scroll', function () {
        sessionStorage.setItem('landing_preview_scroll', window.scrollY);
    });

    (function () {
        const savedScroll = sessionStorage.getItem('landing_preview_scroll');
        if (savedScroll && parseInt(savedScroll) > 0) {
            const targetScroll = parseInt(savedScroll);
            const restoreScroll = () => window.scrollTo({ top: targetScroll, behavior: 'instant' });

            restoreScroll();
            document.addEventListener('DOMContentLoaded', restoreScroll);
            window.addEventListener('load', restoreScroll);

            // Re-apply in case images push content down
            setTimeout(restoreScroll, 100);
            setTimeout(restoreScroll, 300);
            setTimeout(restoreScroll, 500);
            setTimeout(restoreScroll, 1000);
        }
    })();
</script>
