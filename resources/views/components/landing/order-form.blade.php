<div class="max-w-7xl mx-auto bg-white p-6 md:p-12 rounded-xl shadow-sm">

    {{-- Header --}}
    <div class="text-center mb-10">
        <h2 class="text-2xl md:text-3xl  mb-5">
            Provide the correct information to order
        </h2>
        <div class="inline-block bg-[#1f8a54] text-white px-10 py-3 rounded-lg text-xl md:text-2xl  shadow-md">
            Fill out the form below
        </div>
    </div>

    {{-- Product Selection Bar --}}
    <div
        class="border border-gray-200 rounded-xl p-4 mb-10 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <input type="radio" checked class="w-5 h-5 accent-red-600 flex-shrink-0">
            <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                <img src="https://via.placeholder.com/100x100/eee/999?text=P" class="w-full h-full object-cover"
                    alt="product">
            </div>
            <span class="text-lg md:text-lg ">
                Fun Flash Card Combo for Kids 🔥
            </span>
        </div>

        <div class="flex items-center gap-6 flex-shrink-0">
            {{-- Qty --}}
            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white">
                <button type="button" onclick="changeQty(-1)"
                    class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 border-r border-gray-300 text-lg font-bold">−</button>
                <span id="qty-display" class="w-10 text-center font-bold text-lg">1</span>
                <button type="button" onclick="changeQty(1)"
                    class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 border-l border-gray-300 text-lg font-bold">+</button>
            </div>
            <span class="text-lg " id="unit-price-display">999.00৳</span>
        </div>
    </div>

    {{-- Form + Summary Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

        {{-- LEFT: Billing Form --}}
        <div class="lg:col-span-7 space-y-6">

            <div>
                <label class="block text-lg  mb-2">
                    Enter your name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="f-name" placeholder=""
                    class="w-full border-2 border-dashed border-gray-300 rounded-xl p-4 bg-white focus:border-[#1f8a54] outline-none transition-all text-lg"
                    style="font-family:'Noto Sans Bengali',sans-serif;">
            </div>

            <div>
                <label class="block text-lg  mb-2">
                    Enter mobile number <span class="text-red-500">*</span>
                </label>
                <input type="tel" id="f-phone" placeholder=""
                    class="w-full border-2 border-dashed border-gray-300 rounded-xl p-4 bg-white focus:border-[#1f8a54] outline-none transition-all text-lg"
                    style="font-family:'Noto Sans Bengali',sans-serif;">
            </div>

            <div>
                <label class="block text-lg  mb-2">
                    Enter district, police station, and area name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="f-address" placeholder=""
                    class="w-full border-2 border-dashed border-gray-300 rounded-xl p-4 bg-white focus:border-[#1f8a54] outline-none transition-all text-lg"
                    style="font-family:'Noto Sans Bengali',sans-serif;">
            </div>

            <div>
                <label class="block text-lg  mb-2">Country (Optional)</label>
                <select
                    class="w-full border-2 border-dashed border-gray-300 rounded-xl p-4 bg-white focus:border-[#1f8a54] outline-none text-lg">
                    <option>Bangladesh</option>
                </select>
            </div>

            {{-- Delivery Charge --}}
            <div class="flex justify-between items-center border border-gray-200 rounded-xl px-5 py-4">
                <span class="text-red-600  text-lg">
                    Delivery charge:
                </span>
                <span class=" text-lg">100.00৳</span>
            </div>
        </div>

        {{-- RIGHT: Order Summary --}}
        <div class="lg:col-span-5">
            <div class="bg-white p-6 sticky top-8">

                {{-- Table Header --}}
                <div
                    class="flex justify-between text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 pb-3 border-b border-dashed border-gray-200">
                    <span>Product</span>
                    <span>Subtotal</span>
                </div>

                {{-- Product Row --}}
                <div class="flex justify-between items-start gap-3 pb-5 mb-5 border-b border-dashed border-gray-200">
                    <div class="flex items-center gap-3">
                        <img src="https://via.placeholder.com/50x50/eee/999?text=P"
                            class="w-12 h-12 rounded-lg flex-shrink-0 object-cover" alt="">
                        <span class="text-sm font-bold leading-snug">
                            Fun educational 235 flash card combo for kids
                        </span>
                    </div>
                    <span class="text-sm  whitespace-nowrap">
                        ×<span id="summary-qty">1</span> <span id="summary-subtotal">999.00</span>৳
                    </span>
                </div>

                {{-- Total --}}
                <div class="flex justify-between items-center text-lg  mb-6">
                    <span>Total</span>
                    <span id="summary-total">1,099.00৳</span>
                </div>

                {{-- COD Box --}}
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6">
                    <h5 class=" text-lg mb-2">
                        Cash on Delivery
                    </h5>
                    <div class="bg-gray-100 rounded-xl p-3 text-xs leading-relaxed text-gray-600">
                        💯 Order with total confidence — pay only after checking the product and being satisfied
                    </div>
                </div>

                {{-- CTA Button --}}
                <button type="button" onclick="submitOrder()"
                    class="w-full bg-[#1f8a54] hover:bg-[#176840] active:translate-y-1 text-white  py-5 rounded-[18px] text-lg md:text-xl shadow-[0_7px_0_0_#124d2f] hover:shadow-[0_3px_0_0_#124d2f] transition-all">
                    Place Order <span id="btn-total">1,099.00</span>৳
                </button>

            </div>
        </div>

    </div>
</div>
