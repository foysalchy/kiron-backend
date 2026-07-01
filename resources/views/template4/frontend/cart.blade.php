@extends('template4.layouts.front')

@section('content')
    <nav aria-label="Breadcrumb"
        class="container mx-auto px-4 flex flex-wrap items-center pt-2 md:pt-4 gap-1 md:gap-2 text-xs sm:text-sm md:text-base lg:text-lg mb-4 md:mb-6">
        <a href="#" class="text-[#632085] hover:text-[#52166d] transition font-medium">Home</a>
        <span class="text-gray-400">/</span>
        <a href="#" class="text-[#632085] hover:text-[#52166d] transition font-medium">Shoping Cart</a>

    </nav>
    <div class="w-full bg-white">
        <div class="container mx-auto mt-6 px-4 py-4 md:py-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-start">

                <!-- ════════════════════════════════════════
                             LEFT SIDE: Product List Card (col-span-7)
                            ════════════════════════════════════════ -->
                <section class="lg:col-span-7 bg-white p-4 sm:p-5 md:p-6 rounded-lg border border-gray-100 shadow-sm">

                    <!-- Table Headers (Hidden on small mobile, visible on sm and up) -->
                    <div
                        class="hidden sm:grid grid-cols-12 pb-3 md:pb-4 border-b border-gray-200 text-base md:text-lg lg:text-xl font-semibold text-gray-500">
                        <div class="col-span-6">Product</div>
                        <div class="col-span-3 text-center">Quantity</div>
                        <div class="col-span-3 text-right">Price</div>
                    </div>

                    <!-- Product Items List -->
                    <div id="cart-items-container" class="divide-y divide-gray-100">

                        <!-- Item 1 -->
                        <div class="cart-item grid grid-cols-12 gap-3 md:gap-4 items-center py-4 md:py-6"
                            data-price="500.00">
                            <!-- Product Info -->
                            <div class="col-span-12 sm:col-span-6 flex gap-3 md:gap-4 items-center">
                                <img src="https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?q=80&w=200"
                                    alt="Feeding Bottle"
                                    class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-xl border border-gray-100 shrink-0" />
                                <div class="min-w-0">
                                    <h4 class="text-base md:text-lg lg:text-xl font-bold text-gray-900 truncate">Luxury
                                        Essentials for Growing Fa...</h4>
                                    <p class="text-xs md:text-sm text-gray-500 mt-0.5">Feeding Item baby and mom</p>
                                    <button
                                        class="remove-btn text-xs md:text-sm text-red-500 hover:text-red-700 flex items-center gap-1 mt-1.5 md:mt-2 transition font-medium focus:outline-none">
                                        <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <!-- Quantity Control -->
                            <div class="col-span-6 sm:col-span-3 flex justify-start sm:justify-center mt-2 sm:mt-0">
                                <div class="flex items-center gap-2 md:gap-3">
                                    <button
                                        class="inc-btn w-7 h-7 md:w-8 md:h-8 flex items-center justify-center border border-[#632085] rounded hover:bg-purple-50 transition text-base md:text-lg font-bold focus:outline-none">-</button>
                                    <span
                                        class="qty-val w-6 text-center font-bold text-gray-800 text-base md:text-xl">1</span>
                                    <button
                                        class="inc-btn w-7 h-7 md:w-8 md:h-8 flex items-center justify-center border border-[#632085] rounded hover:bg-purple-50 transition text-base md:text-lg font-bold focus:outline-none">+</button>
                                </div>
                            </div>
                            <!-- Price -->
                            <div class="col-span-6 sm:col-span-3 text-right mt-2 sm:mt-0">
                                <span class="text-base md:text-lg lg:text-xl font-bold text-gray-900">BDT <span
                                        class="item-total-price">500.00</span></span>
                            </div>
                        </div>

                        <!-- Item 2 -->
                        <div class="cart-item grid grid-cols-12 gap-3 md:gap-4 items-center py-4 md:py-6"
                            data-price="500.00">
                            <!-- Product Info -->
                            <div class="col-span-12 sm:col-span-6 flex gap-3 md:gap-4 items-center">
                                <img src="https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?q=80&w=200"
                                    alt="Feeding Bottle"
                                    class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-xl border border-gray-100 shrink-0" />
                                <div class="min-w-0">
                                    <h4 class="text-base md:text-lg lg:text-xl font-bold text-gray-900 truncate">Luxury
                                        Essentials for Growing Fa...</h4>
                                    <p class="text-xs md:text-sm text-gray-500 mt-0.5">Feeding Item baby and mom</p>
                                    <button
                                        class="remove-btn text-xs md:text-sm text-red-500 hover:text-red-700 flex items-center gap-1 mt-1.5 md:mt-2 transition font-medium focus:outline-none">
                                        <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <!-- Quantity Control -->
                            <div class="col-span-6 sm:col-span-3 flex justify-start sm:justify-center mt-2 sm:mt-0">
                                <div class="flex items-center gap-2 md:gap-3">
                                    <button
                                        class="inc-btn w-7 h-7 md:w-8 md:h-8 flex items-center justify-center border border-[#632085] rounded hover:bg-purple-50 transition text-base md:text-lg font-bold focus:outline-none">-</button>
                                    <span
                                        class="qty-val w-6 text-center font-bold text-gray-800 text-base md:text-xl">1</span>
                                    <button
                                        class="inc-btn w-7 h-7 md:w-8 md:h-8 flex items-center justify-center border border-[#632085] rounded hover:bg-purple-50 transition text-base md:text-lg font-bold focus:outline-none">+</button>
                                </div>
                            </div>
                            <!-- Price -->
                            <div class="col-span-6 sm:col-span-3 text-right mt-2 sm:mt-0">
                                <span class="text-base md:text-lg lg:text-xl font-bold text-gray-900">BDT <span
                                        class="item-total-price">500.00</span></span>
                            </div>
                        </div>

                        <!-- Item 3 -->
                        <div class="cart-item grid grid-cols-12 gap-3 md:gap-4 items-center py-4 md:py-6"
                            data-price="500.00">
                            <!-- Product Info -->
                            <div class="col-span-12 sm:col-span-6 flex gap-3 md:gap-4 items-center">
                                <img src="https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?q=80&w=200"
                                    alt="Feeding Bottle"
                                    class="w-16 h-16 md:w-20 md:h-20 object-cover rounded-xl border border-gray-100 shrink-0" />
                                <div class="min-w-0">
                                    <h4 class="text-base md:text-lg lg:text-xl font-bold text-gray-900 truncate">Luxury
                                        Essentials for Growing Fa...</h4>
                                    <p class="text-xs md:text-sm text-gray-500 mt-0.5">Feeding Item baby and mom</p>
                                    <button
                                        class="remove-btn text-xs md:text-sm text-red-500 hover:text-red-700 flex items-center gap-1 mt-1.5 md:mt-2 transition font-medium focus:outline-none">
                                        <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <!-- Quantity Control -->
                            <div class="col-span-6 sm:col-span-3 flex justify-start sm:justify-center mt-2 sm:mt-0">
                                <div class="flex items-center gap-2 md:gap-3">
                                    <button
                                        class="inc-btn w-7 h-7 md:w-8 md:h-8 flex items-center justify-center border border-[#632085] rounded hover:bg-purple-50 transition text-base md:text-lg font-bold focus:outline-none">-</button>
                                    <span
                                        class="qty-val w-6 text-center font-bold text-gray-800 text-base md:text-xl">1</span>
                                    <button
                                        class="inc-btn w-7 h-7 md:w-8 md:h-8 flex items-center justify-center border border-[#632085] rounded hover:bg-purple-50 transition text-base md:text-lg font-bold focus:outline-none">+</button>
                                </div>
                            </div>
                            <!-- Price -->
                            <div class="col-span-6 sm:col-span-3 text-right mt-2 sm:mt-0">
                                <span class="text-base md:text-lg lg:text-xl font-bold text-gray-900">BDT <span
                                        class="item-total-price">500.00</span></span>
                            </div>
                        </div>

                    </div>

                    <!-- Continue Shopping Button -->
                    <div class="mt-4 md:mt-6">
                        <button
                            class="w-full bg-gray-200 hover:bg-gray-300 transition text-[#0f172a] font-semibold py-3 md:py-4 px-4 md:px-6 rounded-xl text-center text-base md:text-lg lg:text-xl focus:outline-none">
                            Continue Shopping
                        </button>
                    </div>

                </section>

                <!-- ════════════════════════════════════════
                             RIGHT SIDE: Order Summary Card (col-span-5)
                            ════════════════════════════════════════ -->
                <section class="lg:col-span-5 bg-white p-4 sm:p-5 md:p-6 rounded-lg border border-gray-100 shadow-sm">

                    <h3 class="text-lg md:text-xl font-bold text-[#0f172a] mb-4 md:mb-6">Order Summary</h3>

                    <!-- Subtotal Row -->
                    <div class="flex justify-between items-center py-3 md:py-4 border-b border-gray-100">
                        <span class="text-base md:text-lg lg:text-xl text-gray-600">Subtotal</span>
                        <span class="text-base md:text-lg lg:text-xl font-bold text-gray-900">BDT <span
                                id="summary-subtotal">2000.00</span></span>
                    </div>

                    <!-- Shipping Rows (Radio Selection Stack) -->
                    <div class="py-4 md:py-5 border-b border-gray-100 w-full">
                        <div class="flex flex-col sm:flex-row items-start sm:justify-between gap-3 md:gap-4 w-full">
                            <span class="text-sm md:text-base text-gray-600 sm:mt-0.5 shrink-0">Shipping</span>

                            <div class="flex flex-col gap-3 w-full ml-auto">

                                <!-- Shipping Option 1 (Default Selected) -->
                                <label
                                    class="flex items-center justify-between sm:justify-end gap-3 cursor-pointer text-xs md:text-sm text-gray-700 hover:text-gray-900 w-full">
                                    <span class="text-left sm:text-right leading-tight">First Class Shipping (3-5 Days
                                        Delivery
                                        Time) : BDT 200.95</span>
                                    <input type="radio" name="shipping_method" value="200.95" checked
                                        class="shrink-0 w-4 h-4 text-[#632085] focus:ring-[#632085] border-gray-300" />
                                </label>

                                <!-- Shipping Option 2 -->
                                <label
                                    class="flex items-center justify-between sm:justify-end gap-3 cursor-pointer text-xs md:text-sm text-gray-700 hover:text-gray-900 w-full">
                                    <span class="text-left sm:text-right leading-tight">Expedited Shipping (2-3 Days
                                        Delivery
                                        Time) : BDT 200.95</span>
                                    <input type="radio" name="shipping_method" value="200.95"
                                        class="shrink-0 w-4 h-4 text-[#632085] focus:ring-[#632085] border-gray-300" />
                                </label>

                                <!-- Shipping Option 3 -->
                                <label
                                    class="flex items-center justify-between sm:justify-end gap-3 cursor-pointer text-xs md:text-sm text-gray-700 hover:text-gray-900 w-full">
                                    <span class="text-left sm:text-right leading-tight">Next Day Air (1 Day Delivery Time)
                                        :
                                        BDT 200.95</span>
                                    <input type="radio" name="shipping_method" value="200.95"
                                        class="shrink-0 w-4 h-4 text-[#632085] focus:ring-[#632085] border-gray-300" />
                                </label>

                                <!-- Shipping Option 4 -->
                                <label
                                    class="flex items-center justify-between sm:justify-end gap-3 cursor-pointer text-xs md:text-sm text-gray-700 hover:text-gray-900 w-full">
                                    <span class="text-left sm:text-right leading-tight">Local Same Day Delivery (Miami) :
                                        BDT
                                        200.95</span>
                                    <input type="radio" name="shipping_method" value="200.95"
                                        class="shrink-0 w-4 h-4 text-[#632085] focus:ring-[#632085] border-gray-300" />
                                </label>

                            </div>
                        </div>
                    </div>

                    <!-- Tax Row -->
                    <div class="flex justify-between items-center py-3 md:py-4 border-b border-gray-100">
                        <span class="text-sm md:text-base text-gray-600">Tax</span>
                        <span class="text-sm md:text-base font-bold text-gray-900">BDT <span
                                id="summary-tax">52.72</span></span>
                    </div>

                    <!-- Total Row (Blue Accent text) -->
                    <div class="flex justify-between items-center py-4 md:py-5">
                        <span class="text-base md:text-lg font-medium text-gray-500">Total</span>
                        <span class="text-xl md:text-2xl font-extrabold text-[#1147aa]">BDT <span
                                id="summary-total">2253.67</span></span>
                    </div>

                    <!-- Order Now Button (Purple Theme) -->
                    <div class="mt-2 md:mt-4">
                        <button
                            class="w-full bg-[#632085] hover:bg-[#52166d] transition text-white font-bold py-3 md:py-4 px-4 md:px-6 rounded-xl text-center text-base md:text-lg lg:text-xl shadow-md focus:outline-none">
                            Order Now
                        </button>
                    </div>

                </section>

            </div>
        </div>
    </div>

    <!-- OUR FEATURED PRODUCTS SECTION -->
    <section class="w-full bg-[#fcfcfc] px-4">
        <div class="container mx-auto py-4 md:py-10">
            <!-- Section Title -->
            <h2 class="text-2xl font-semibold text-[#041533] mb-12 tracking-tight">
                Our Featured Products
            </h2>

            <!-- Products Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                    <div class="relative">
                        <div
                            class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                            <div class="w-full h-full p-8 flex items-center justify-center">
                                <img src="{{ asset('images/babyshop/images/girl.png') }}" alt="Product Image"
                                    class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                            </div>
                        </div>

                        <!-- উইশলিস্ট বাটন -->
                        <div class="absolute -top-1 -right-1">
                            <div class="bg-white p-1 rounded-full">
                                <button
                                    class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                    aria-label="Add to Wishlist">
                                    <i class="fa-regular fa-heart text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1 p-4">
                        <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                            Luxury Essentials for Growing Families
                        </h3>

                        <!-- Price Section inspired by the image -->
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                            <!-- Prices -->
                            <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">৳1,040</span>
                            <span class="text-[#999999] text-sm md:text-base line-through">৳1,340</span>

                            <div class="basis-full h-0 sm:hidden"></div>

                            <!-- Discount Percentage Badge -->
                            <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                -22%
                            </span>
                        </div>
                        <button
                            class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                            Add To Cart
                        </button>
                    </div>
                </div>
                <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                    <div class="relative">
                        <div
                            class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                            <div class="w-full h-full p-8 flex items-center justify-center">
                                <img src="{{ asset('images/babyshop/images/girl.png') }}" alt="Product Image"
                                    class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                            </div>
                        </div>

                        <!-- উইশলিস্ট বাটন -->
                        <div class="absolute -top-1 -right-1">
                            <div class="bg-white p-1 rounded-full">
                                <button
                                    class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                    aria-label="Add to Wishlist">
                                    <i class="fa-regular fa-heart text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1 p-4">
                        <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                            Luxury Essentials for Growing Families
                        </h3>

                        <!-- Price Section inspired by the image -->
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                            <!-- Prices -->
                            <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">৳1,040</span>
                            <span class="text-[#999999] text-sm md:text-base line-through">৳1,340</span>

                            <div class="basis-full h-0 sm:hidden"></div>

                            <!-- Discount Percentage Badge -->
                            <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                -22%
                            </span>
                        </div>
                        <button
                            class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                            Add To Cart
                        </button>
                    </div>
                </div>
                <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                    <div class="relative">
                        <div
                            class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                            <div class="w-full h-full p-8 flex items-center justify-center">
                                <img src="{{ asset('images/babyshop/images/girl.png') }}" alt="Product Image"
                                    class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                            </div>
                        </div>

                        <!-- উইশলিস্ট বাটন -->
                        <div class="absolute -top-1 -right-1">
                            <div class="bg-white p-1 rounded-full">
                                <button
                                    class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                    aria-label="Add to Wishlist">
                                    <i class="fa-regular fa-heart text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1 p-4">
                        <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                            Luxury Essentials for Growing Families
                        </h3>

                        <!-- Price Section inspired by the image -->
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                            <!-- Prices -->
                            <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">৳1,040</span>
                            <span class="text-[#999999] text-sm md:text-base line-through">৳1,340</span>

                            <div class="basis-full h-0 sm:hidden"></div>

                            <!-- Discount Percentage Badge -->
                            <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                -22%
                            </span>
                        </div>
                        <button
                            class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                            Add To Cart
                        </button>
                    </div>
                </div>
                <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                    <div class="relative">
                        <div
                            class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                            <div class="w-full h-full p-8 flex items-center justify-center">
                                <img src="{{ asset('images/babyshop/images/girl.png') }}" alt="Product Image"
                                    class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                            </div>
                        </div>

                        <!-- উইশলিস্ট বাটন -->
                        <div class="absolute -top-1 -right-1">
                            <div class="bg-white p-1 rounded-full">
                                <button
                                    class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                    aria-label="Add to Wishlist">
                                    <i class="fa-regular fa-heart text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1 p-4">
                        <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                            Luxury Essentials for Growing Families
                        </h3>

                        <!-- Price Section inspired by the image -->
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                            <!-- Prices -->
                            <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">৳1,040</span>
                            <span class="text-[#999999] text-sm md:text-base line-through">৳1,340</span>

                            <div class="basis-full h-0 sm:hidden"></div>

                            <!-- Discount Percentage Badge -->
                            <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                -22%
                            </span>
                        </div>
                        <button
                            class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                            Add To Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {

      const taxRate = 52.72; // Static Tax matching the image value
      const cartContainer = document.getElementById("cart-items-container");
      const shippingRadios = document.querySelectorAll("input[name='shipping_method']");

      // Function to calculate and update totals
      function updateCartCalculations() {
        const cartItems = document.querySelectorAll(".cart-item");
        let subtotal = 0;

        cartItems.forEach(item => {
          const basePrice = parseFloat(item.getAttribute("data-price"));
          const qty = parseInt(item.querySelector(".qty-val").textContent);
          const itemTotalPrice = basePrice * qty;

          // Update current item price text
          item.querySelector(".item-total-price").textContent = itemTotalPrice.toFixed(2);

          subtotal += itemTotalPrice;
        });

        // Get selected shipping fee
        let selectedShipping = 0;
        shippingRadios.forEach(radio => {
          if (radio.checked) {
            selectedShipping = parseFloat(radio.value);
          }
        });

        // If no items left in the cart
        if (cartItems.length === 0) {
          selectedShipping = 0;
          document.getElementById("summary-tax").textContent = "0.00";
          document.getElementById("summary-subtotal").textContent = "0.00";
          document.getElementById("summary-total").textContent = "0.00";
          return;
        }

        // Calculate final total
        const finalTotal = subtotal + selectedShipping + taxRate;

        // Update DOM text
        document.getElementById("summary-subtotal").textContent = subtotal.toFixed(2);
        document.getElementById("summary-tax").textContent = taxRate.toFixed(2);
        document.getElementById("summary-total").textContent = finalTotal.toFixed(2);
      }

      // Bind Event Listeners to each cart item
      function initializeCartEvents() {
        const cartItems = document.querySelectorAll(".cart-item");

        cartItems.forEach(item => {
          const decBtn = item.querySelector(".dec-btn");
          const incBtn = item.querySelector(".inc-btn");
          const qtyVal = item.querySelector(".qty-val");
          const removeBtn = item.querySelector(".remove-btn");

          // Decrease Quantity
          decBtn.addEventListener("click", function () {
            let qty = parseInt(qtyVal.textContent);
            if (qty > 1) {
              qty--;
              qtyVal.textContent = qty;
              updateCartCalculations();
            }
          });

          // Increase Quantity
          incBtn.addEventListener("click", function () {
            let qty = parseInt(qtyVal.textContent);
            qty++;
            qtyVal.textContent = qty;
            updateCartCalculations();
          });

          // Remove Product Item
          removeBtn.addEventListener("click", function () {
            // Fade out animation
            item.style.transition = "all 0.3s ease";
            item.style.opacity = "0";
            item.style.transform = "translateX(-20px)";

            setTimeout(() => {
              item.remove();
              updateCartCalculations();
            }, 300);
          });
        });

        // Shipping methods change listener
        shippingRadios.forEach(radio => {
          radio.addEventListener("change", updateCartCalculations);
        });
      }

      // Run initial startup calculations
      initializeCartEvents();
      updateCartCalculations();
    });
  </script>
@endpush
