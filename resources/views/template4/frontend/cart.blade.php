@extends('template4.layouts.front')

@section('content')
    <nav aria-label="Breadcrumb"
        class="container mx-auto px-4 flex flex-wrap items-center pt-2 md:pt-4 gap-1 md:gap-2 text-xs sm:text-sm md:text-base lg:text-lg mb-4 md:mb-6">
        <a href="/" class="text-[#632085] hover:text-[#52166d] transition font-medium">Home</a>
        <span class="text-gray-400">/</span>
        <span class="text-[#632085] hover:text-[#52166d] transition font-medium">Shopping Cart</span>
    </nav>

    <div class="w-full bg-white">
        <div class="container mx-auto mt-6 px-4 py-4 md:py-10">
            @if (Cart::count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-start">

                    <!-- ════════ LEFT SIDE: Product List ════════ -->
                    <section class="lg:col-span-7 bg-white p-4 sm:p-5 md:p-6 rounded-lg border border-gray-100 shadow-sm">

                        <div class="hidden sm:grid grid-cols-12 pb-3 md:pb-4 border-b border-gray-200 text-base md:text-lg lg:text-xl font-semibold text-gray-500">
                            <div class="col-span-6">Product</div>
                            <div class="col-span-3 text-center">Quantity</div>
                            <div class="col-span-3 text-right">Price</div>
                        </div>

                        <div id="cart-items-container" class="divide-y divide-gray-100">
                            @foreach ($cartContent as $item)
                                <div class="cart-item grid grid-cols-12 gap-3 md:gap-4 items-center py-4 md:py-6">
                                    <div class="col-span-12 sm:col-span-6 flex gap-3 md:gap-4 items-center">
                                        <img src="{{ $item->options->thumbnail ?? '' }}" alt="cart thumbnail" loading="lazy" height="" width=""
                                            onerror="this.src='{{ $item->options->thumbnail ?? '' }}'"
                                            class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-xl border border-gray-100 shrink-0" />
                                        <div class="min-w-0">
                                            <h4 class="text-base md:text-lg lg:text-xl font-bold text-gray-900 truncate">{{ $item->name }}</h4>
                                            <p class="text-xs md:text-sm text-gray-500 mt-0.5">{{ $item->options->variant ?? '' }}</p>
                                            <a href="{{ route('cart.remove', $item->rowId) }}"
                                                class="remove-btn text-xs md:text-sm text-red-500 hover:text-red-700 flex items-center gap-1 mt-1.5 md:mt-2 transition font-medium focus:outline-none">
                                                <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                Remove
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-span-6 sm:col-span-3 flex justify-start sm:justify-center mt-2 sm:mt-0">
                                        <div class="flex items-center gap-2 md:gap-3">
                                            <button onclick="updateCartQty('{{ $item->rowId }}', {{ $item->qty - 1 }})"
                                                class="w-7 h-7 md:w-8 md:h-8 flex items-center justify-center border border-[#632085] rounded hover:bg-purple-50 transition text-base md:text-lg font-bold focus:outline-none">-</button>
                                            <span class="qty-val w-6 text-center font-bold text-gray-800 text-base md:text-xl">{{ $item->qty }}</span>
                                            <button onclick="updateCartQty('{{ $item->rowId }}', {{ $item->qty + 1 }})"
                                                class="w-7 h-7 md:w-8 md:h-8 flex items-center justify-center border border-[#632085] rounded hover:bg-purple-50 transition text-base md:text-lg font-bold focus:outline-none">+</button>
                                        </div>
                                    </div>
                                    <div class="col-span-6 sm:col-span-3 text-right mt-2 sm:mt-0">
                                        <span class="text-base md:text-lg lg:text-xl font-bold text-gray-900">{{ $setup->currency }} <span class="item-total-price">{{ number_format($item->subtotal) }}</span></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 md:mt-6">
                            <a href="{{ route('shop.index') }}" class="block w-full bg-gray-200 hover:bg-gray-300 transition text-[#0f172a] font-semibold py-3 md:py-4 px-4 md:px-6 rounded-xl text-center text-base md:text-lg lg:text-xl">
                                Continue Shopping
                            </a>
                        </div>
                    </section>

                    <!-- ════════ RIGHT SIDE: Order Summary ════════ -->
                    <section class="lg:col-span-5 bg-white p-4 sm:p-5 md:p-6 rounded-lg border border-gray-100 shadow-sm">
                        <h3 class="text-lg md:text-xl font-bold text-[#0f172a] mb-4 md:mb-6">Order Summary</h3>

                        <div class="flex justify-between items-center py-3 md:py-4 border-b border-gray-100">
                            <span class="text-base md:text-lg lg:text-xl text-gray-600">Subtotal</span>
                            <span class="text-base md:text-lg lg:text-xl font-bold text-gray-900">{{ $setup->currency }} <span id="summary-subtotal">{{ number_format($subtotal) }}</span></span>
                        </div>

                        <!-- Shipping Selection -->
                        <div class="py-4 md:py-5 border-b border-gray-100 w-full">
                            <form action="{{ route('cart.shipping') }}" method="POST" id="shipping-form">
                                @csrf
                                <div class="flex flex-col sm:flex-row items-start sm:justify-between gap-3 md:gap-4 w-full">
                                    <span class="text-sm md:text-base text-gray-600 sm:mt-0.5 shrink-0">Shipping</span>
                                    <div class="flex flex-col gap-3 w-full ml-auto">
                                        <label class="flex items-center justify-between sm:justify-end gap-3 cursor-pointer text-xs md:text-sm text-gray-700 hover:text-gray-900 w-full">
                                            <span class="text-left sm:text-right leading-tight">Inside Dhaka: {{ $setup->currency }} {{ number_format($setup->inside_charge) }}</span>
                                            <input type="radio" name="area" value="inside" onchange="this.form.submit()" {{ $shipping_area == 'inside' ? 'checked' : '' }} class="shrink-0 w-4 h-4 text-[#632085] focus:ring-[#632085] border-gray-300" />
                                        </label>
                                        <label class="flex items-center justify-between sm:justify-end gap-3 cursor-pointer text-xs md:text-sm text-gray-700 hover:text-gray-900 w-full">
                                            <span class="text-left sm:text-right leading-tight">Outside Dhaka: {{ $setup->currency }} {{ number_format($setup->outside_charge) }}</span>
                                            <input type="radio" name="area" value="outside" onchange="this.form.submit()" {{ $shipping_area == 'outside' ? 'checked' : '' }} class="shrink-0 w-4 h-4 text-[#632085] focus:ring-[#632085] border-gray-300" />
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tax & Discount -->
                        <div class="space-y-2 py-3 border-b border-gray-100">
                            @if ($discount > 0)
                                <div class="flex justify-between items-center text-green-600">
                                    <span class="text-sm md:text-base">Discount ({{ session('coupon')['coupon_code'] }})</span>
                                    <span class="text-sm md:text-base font-bold">- {{ $setup->currency }} {{ number_format($discount) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center">
                                <span class="text-sm md:text-base text-gray-600">Tax</span>
                                <span class="text-sm md:text-base font-bold text-gray-900">{{ $setup->currency }} 0.00</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center py-4 md:py-5">
                            <span class="text-base md:text-lg font-medium text-gray-500">Total</span>
                            <span class="text-xl md:text-2xl font-extrabold text-[#1147aa]">{{ $setup->currency }} <span id="summary-total">{{ number_format($total) }}</span></span>
                        </div>

                        <div class="mt-2 md:mt-4">
                            <a href="{{ route('checkout.index') }}"
                                class="block w-full bg-[#632085] hover:bg-[#52166d] transition text-white font-bold py-3 md:py-4 px-4 md:px-6 rounded-xl text-center text-base md:text-lg lg:text-xl shadow-md focus:outline-none">
                                Checkout Now
                            </a>
                        </div>
                    </section>
                </div>
            @else
                <!-- Empty Cart State (Design-consistent) -->
                <div class="bg-white p-20 text-center rounded-lg border border-dashed border-gray-300">
                    <h2 class="text-2xl font-bold text-gray-800">Your cart is currently empty!</h2>
                    <a href="{{ route('shop.index') }}" class="inline-block mt-6 bg-[#632085] text-white px-10 py-3 rounded-xl font-bold">Start Shopping</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Featured Products Loop -->
    <section class="w-full bg-[#fcfcfc] px-4">
        <div class="container mx-auto py-4 md:py-10">
            <h2 class="text-2xl font-semibold text-[#041533] mb-12 tracking-tight">Our Featured Products</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($relatedProducts->take(4) as $product)
                    <x-template1.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    <!-- Hidden form for update logic -->
    <form id="update-cart-form" action="{{ route('cart.update') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="rowId" id="update-row-id">
        <input type="hidden" name="qty" id="update-qty">
    </form>
@endsection

@push('scripts')
    <script>
        function updateCartQty(rowId, newQty) {
            if (newQty < 1) return;
            document.getElementById('update-row-id').value = rowId;
            document.getElementById('update-qty').value = newQty;
            document.getElementById('update-cart-form').submit();
        }
    </script>
@endpush
