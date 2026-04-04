@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto ">
        <!-- Cart Top Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                শপিং কার্ট (<span id="header-qty">1</span> টি পণ্য)
            </h1>
            <a href="/"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 transition-all">
                <i class="fas fa-arrow-left text-xs"></i> শপিং চালিয়ে যান
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- LEFT: Cart Items List -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="p-4">
                        <h2 class="text-2xl font-semibold leading-none tracking-tight">কার্টের পণ্যসমূহ</h2>
                    </div>

                    <div class="p-6">
                        <!-- Product Item -->
                        <div
                            class="flex flex-col md:flex-row items-center gap-6 p-4 border border-gray-200 rounded-lg relative">
                            <!-- Image -->
                            <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                <img src="https://images.unsplash.com/photo-1616788494707-ec28f08d05a1?auto=format&fit=crop&q=80&w=300"
                                    alt="Universal Car Mobile Holder" class="w-full h-full object-cover">
                            </div>

                            <!-- Info -->
                            <div class="flex-1 text-center md:text-left">
                                <h3 class="font-medium text-gray-700 text-xl mb-1">Car Shape Mobile Holder - Universal</h3>
                                <span
                                    class="inline-block bg-orange-50 text-[#FF6A00] text-xs font-medium px-3 py-1 rounded-full mb-2 uppercase">Electronics</span>
                                <p class="text-lg font-bold text-gray-900">৳<span id="unit-price">750</span></p>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex items-center gap-1 bg-gray-50 border border-gray-200 rounded-lg p-1">
                                <button onclick="changeQty(-1)"
                                    class="w-9 h-9 flex items-center justify-center text-gray-600 hover:bg-white hover:shadow-sm rounded-md transition-all">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <span id="item-qty" class="w-10 text-center font-bold text-gray-800">1</span>
                                <button onclick="changeQty(1)"
                                    class="w-9 h-9 flex items-center justify-center text-gray-600 hover:bg-white hover:shadow-sm rounded-md transition-all">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>

                            <!-- Price & Delete -->
                            <div class="flex flex-col items-center md:items-end gap-3 md:min-w-[100px]">
                                <p class="font-bold text-xl text-[#FF6A00]">৳<span id="line-total">750</span></p>
                                <button
                                    class="w-9 h-9 flex items-center justify-center rounded-lg text-red-500 hover:bg-red-500 hover:text-white transition-all">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs sticky top-24 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900">অর্ডার সামারি</h2>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Coupon Code -->
                        <div class="space-y-3">
                            <label class="text-sm font-bold text-gray-700">কুপন কোড</label>
                            <div class="flex gap-2 mt-2">
                                <input type="text" placeholder="কুপন কোড লিখুন"
                                    class="flex-1 px-4 py-3 border border-gray-200 rounded-lg text-sm outline-none focus:border-[#FF6A00] transition-all">
                                <button
                                    class="w-12 flex items-center justify-center border border-gray-200 text-white rounded-lg hover:bg-[#FF6A00] transition-all">
                                    <i class="fas fa-tag text-gray-900"></i>
                                </button>
                            </div>
                        </div>
                        <div class="border-t border-gray-200"></div>

                        <!-- Cost Breakdown -->
                        <div class="space-y-4 font-semibold text-gray-700">
                            <div class="flex justify-between">
                                <span>সাবটোটাল:</span>
                                <span>৳<span id="summary-subtotal">750</span></span>
                            </div>
                            <div class="flex justify-between">
                                <span>ডেলিভারি চার্জ:</span>
                                <span>৳<span id="summary-shipping">60</span></span>
                            </div>

                            <!-- Shipping Progress/Message -->
                            <div id="shipping-msg-box"
                                class="p-3 rounded-lg text-sm  text-orange-600 flex items-center gap-2">
                                <i class="fas fa-truck-moving"></i>
                                <span id="shipping-text">আরো ৳২৫০ কিনলে ফ্রি ডেলিভারি!</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-200"></div>

                        <!-- Grand Total -->
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-black text-gray-900">মোট:</span>
                            <span class="text-xl font-medium text-[#FF6A00]">৳<span id="summary-total">810</span></span>
                        </div>

                        <!-- Actions -->
                        <div class="space-y-4">
                            <a href="{{url('/checkout')}}"
                                class="block w-full text-center bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-md ">
                                চেকআউট করুন
                            </a>
                            <p class="text-center text-sm font-medium text-gray-600 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-truck h-4 w-4">
                                    <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                                    <path d="M15 18H9"></path>
                                    <path
                                        d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                                    </path>
                                    <circle cx="17" cy="18" r="2"></circle>
                                    <circle cx="7" cy="18" r="2"></circle>
                                </svg>
                                ২-৩ দিনে ডেলিভারি
                            </p>
                        </div>

                        <!-- Trust Badges -->
                        <div class="pt-6 border-t border-gray-100">
                            <div
                                class="grid grid-cols-2 gap-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span> নিরাপদ পেমেন্ট
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span> ফ্রি রিটার্ন
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-[#FF6A00] rounded-full"></span> ২৪/৭ সাপোর্ট
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 bg-purple-500 rounded-full"></span> গুণগত মান
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        let qty = 1;
        const unitPrice = 750;
        const shippingCharge = 60;
        const freeShippingThreshold = 1000;

        function updateCart() {
            const subtotal = qty * unitPrice;
            let currentShipping = subtotal >= freeShippingThreshold ? 0 : shippingCharge;
            const total = subtotal + currentShipping;

            // Display Updates
            document.getElementById('item-qty').innerText = qty;
            document.getElementById('header-qty').innerText = qty;
            document.getElementById('line-total').innerText = subtotal;
            document.getElementById('summary-subtotal').innerText = subtotal;
            document.getElementById('summary-shipping').innerText = currentShipping;
            document.getElementById('summary-total').innerText = total;

            // Shipping Alert Logic
            const msgBox = document.getElementById('shipping-msg-box');
            const msgText = document.getElementById('shipping-text');

            if (subtotal >= freeShippingThreshold) {
                msgText.innerText = "অভিনন্দন! আপনি ফ্রি ডেলিভারি পাচ্ছেন।";
                msgBox.className =
                "p-3 rounded-lg text-[13px] font-bold bg-green-50 text-green-600 flex items-center gap-2";
            } else {
                const remaining = freeShippingThreshold - subtotal;
                msgText.innerText = `আরো ৳${remaining} কিনলে ফ্রি ডেলিভারি!`;
                msgBox.className =
                    "p-3 rounded-lg text-[13px] font-bold bg-orange-50 text-orange-600 flex items-center gap-2";
            }
        }

        function changeQty(amount) {
            qty += amount;
            if (qty < 1) qty = 1;
            updateCart();
        }
    </script>
@endpush
