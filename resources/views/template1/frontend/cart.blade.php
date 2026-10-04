@extends('template1.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.cart-meta', ['setup' => $setup])
@endsection
@section('content')
    <section class="container mx-auto py-4 md:py-6 px-4 lg:px-0">
        @if (\Gloudemans\Shoppingcart\Facades\Cart::count() > 0)
            <!-- Top Header -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">
                    Shopping Cart ({{ \Gloudemans\Shoppingcart\Facades\Cart::count() }} Items)
                </h1>
                <a href="{{ route('shop.index') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded text-sm font-medium text-gray-600 hover:bg-gray-50 transition-all">
                    ← Continue Shopping
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg   shadow-xs overflow-hidden">
                        <div class="p-5">
                            <h2 class="text-xl font-bold text-gray-800">Cart Items</h2>
                        </div>

                        <div class="p-5 space-y-6">
                            @foreach ($cartContent as $item)
                                <div
                                    class="flex flex-row items-start gap-3 md:gap-6 p-3 md:p-4 border border-gray-200 rounded-lg relative group">
                                    <!-- Image -->
                                    <div class="w-16 h-16 md:w-24 md:h-24 bg-gray-50 rounded-lg overflow-hidden shrink-0 ">
                                        <img src="{{ $item->options->thumbnail }}"
                                            onerror="this.src='{{ asset('./images/template1/frontend/default.webp') }}'"
                                            class="w-full h-full object-cover" alt="{{ $item->name ?? 'Product Image' }}">
                                    </div>

                                    <!-- Details -->
                                    <div class="flex-1">
                                        <a href="{{ url($item->options->slug ?? $item->id) }}"
                                            class="group/title">
                                            <h3
                                                class="font-bold text-gray-800 text-sm md:text-lg leading-tight mb-1 group-hover/title:text-[var(--primary-color)] transition-colors">
                                                {{ $item->name ?? '' }}
                                            </h3>
                                        </a>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span
                                                class=" text-[var(--primary-color)] text-xs font-bold px-2 py-0.5 rounded uppercase">
                                                {{ $item->options->variant ?? 'Product' }}
                                            </span>
                                        </div>
                                        <div class="flex items-baseline gap-2">
                                            <p class="font-bold text-gray-700 text-lg">
                                                {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . ' ' . number_format($item->price, 0) : number_format($item->price, 0) . ' ' . $setup->currency }}
                                            </p>

                                            @if (isset($item->options['regular_price']) && (float) $item->options['regular_price'] > (float) $item->price)
                                                <span class="text-sm text-gray-500 line-through font-normal pl-1">
                                                    {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . ' ' . number_format($item->options['regular_price'], 0) : number_format($item->options['regular_price'], 0) . ' ' . $setup->currency }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Qty Controls -->
                                    <div
                                        class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white shadow-xs">
                                        <button onclick="updateCartQty('{{ $item->rowId }}', {{ $item->qty - 1 }})"
                                            class="px-3 py-2 text-gray-600 text-xl hover:text-[var(--primary-color)] hover:bg-gray-50 transition-colors">-</button>
                                        <span class="w-10 text-center font-bold text-gray-800">{{ $item->qty }}</span>
                                        <button onclick="updateCartQty('{{ $item->rowId }}', {{ $item->qty + 1 }})"
                                            class="px-3 py-2 text-gray-600 text-xl hover:text-[var(--primary-color)] hover:bg-gray-50 transition-colors">+</button>
                                    </div>

                                    <!-- Price & Delete -->
                                    <div class="flex flex-col items-end gap-2 md:gap-4 min-w-[70px] md:min-w-[100px]">
                                        <p class="font-black text-base md:text-xl text-[var(--primary-color)]">
                                            {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . ' ' . number_format($item->subtotal, 0) : number_format($item->subtotal, 0) . ' ' . $setup->currency }}
                                        </p>
                                        <a href="{{ route('cart.remove', $item->rowId) }}"
                                            aria-label="Remove {{ $item->name }} from cart"
                                            class="text-red-400 hover:text-red-600 transition-colors">
                                            <i class="far fa-trash-alt text-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg   shadow-xs p-4 md:p-6 lg:sticky lg:top-24">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">Order Summary</h2>

                        <!-- selection shipping area -->
                        <div class="mb-6">
                            <label class="text-sm font-bold text-gray-600 block mb-3">Select Your Shipping Area</label>
                            <form action="{{ route('cart.shipping') }}" method="POST" id="shipping-form">
                                @csrf
                                <div class="space-y-2">
                                    {{-- ১. Inside Charge (Dynamic) --}}
                                    <label
                                        class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all {{ $shipping_area == 'inside' ? 'border-[var(--primary-color)] bg-orange-50' : 'border-gray-100' }}">
                                        <input type="radio" name="area" value="inside" onchange="this.form.submit()"
                                            {{ $shipping_area == 'inside' ? 'checked' : '' }}
                                            class="accent-[var(--primary-color)]">
                                        <span class="text-sm font-bold text-gray-700">
                                            Inside Dhaka ({{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . ' ' . number_format($setup->inside_charge, 0) : number_format($setup->inside_charge, 0) . ' ' . $setup->currency }})
                                        </span>
                                    </label>

                                    {{-- ২. Outside Charge (Dynamic) --}}
                                    <label
                                        class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all {{ $shipping_area == 'outside' ? 'border-[var(--primary-color)] bg-orange-50' : 'border-gray-100' }}">
                                        <input type="radio" name="area" value="outside" onchange="this.form.submit()"
                                            {{ $shipping_area == 'outside' ? 'checked' : '' }}
                                            class="accent-[var(--primary-color)]">
                                        <span class="text-sm font-bold text-gray-700">
                                            Outside Dhaka ({{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . ' ' . number_format($setup->outside_charge, 0) : number_format($setup->outside_charge, 0) . ' ' . $setup->currency }})
                                        </span>
                                    </label>
                                </div>
                            </form>
                        </div>
                        <!-- coupon section -->
                        <form action="{{ route('coupon.apply') }}" method="POST" class="mb-6">
                            @csrf
                            <label class="text-sm font-bold text-gray-600 block mb-2">Coupon Code</label>
                            <div class="flex gap-2">
                                <input type="text" name="coupon_code" placeholder="Enter Coupon Code"
                                    value="{{ session()->has('coupon') ? session('coupon')['coupon_code'] : '' }}"
                                    class="flex-1 border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-[#016738] transition-all"
                                    {{ session()->has('coupon') ? 'readonly' : '' }}>

                                @if (session()->has('coupon'))
                                    <a href="{{ route('coupon.remove') }}"
                                        class="bg-red-500 text-primary rounded-lg px-4 py-2.5 hover:bg-red-600 flex items-center">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @else
                                    <button type="submit"
                                        class="bg-white border border-gray-200 rounded-lg px-4 py-2.5 hover:bg-gray-50 transition-all">
                                        Apply Now
                                    </button>
                                @endif
                            </div>
                        </form>

                        <!-- calcualtion -->
                        <div class="space-y-4 border-t border-gray-100 pt-6 mb-6">
                            <div class="flex justify-between font-bold text-gray-600">
                                <span>Subtotal:</span>
                                <span>{{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . ' ' . number_format($subtotal, 0) : number_format($subtotal, 0) . ' ' . $setup->currency }}</span>
                            </div>

                            @if ($discount > 0)
                                <div class="flex justify-between font-bold text-green-600">
                                    <span>Discount ({{ session('coupon')['coupon_code'] }}):</span>
                                    <span>- {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . ' ' . number_format($discount, 0) : number_format($discount, 0) . ' ' . $setup->currency }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between font-bold text-gray-600">
                                <span>Delivery Charge:</span>
                                <span>{{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . ' ' . number_format($shipping, 0) : number_format($shipping, 0) . ' ' . $setup->currency }}</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6 mb-8 flex justify-between items-center">
                            <span class="text-lg md:text-2xl font-semibold md:font-black text-gray-800">Total:</span>
                            <span class="text-lg md:text-2xl font-semibold md:font-black text-[var(--primary-color)]">
                                {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . ' ' . number_format($total, 0) : number_format($total, 0) . ' ' . $setup->currency }}
                            </span>
                        </div>

                        <!-- Checkout Button -->
                        <a href="{{ url('/checkout') }}"
                            class="block w-full text-center primary-bg hover:bg-[#e65f00] text-primary py-3.5 rounded-xl font-bold text-lg shadow-lg transition-all mb-4">
                            Checkout
                        </a>

                        <p class="text-center text-gray-500 text-sm font-medium flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-truck h-4 w-4">
                                <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                                <path d="M15 18H9"></path>
                                <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                                </path>
                                <circle cx="17" cy="18" r="2"></circle>
                                <circle cx="7" cy="18" r="2"></circle>
                            </svg>
                            Delivery time in 2-3 days
                        </p>

                        <!-- Trust Badges -->

                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-24 bg-white rounded-3xl border border-dashed border-gray-200">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-shopping-basket text-3xl text-gray-200"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Your cart is currently empty!</h2>
                <a href="{{ route('shop.index') }}"
                    class="inline-block mt-8 bg-[var(--primary-color)] text-primary px-10 py-3 rounded-xl font-bold">Start
                    shopping</a>
            </div>
        @endif
    </section>

    <!-- Hidden form for updates -->
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
