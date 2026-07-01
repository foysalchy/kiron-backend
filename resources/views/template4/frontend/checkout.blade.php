@extends('template4.layouts.front')

@section('content')
    <section class="bg-[#F9F9F9] py-2">
        <nav aria-label="Breadcrumb"
            class="container mx-auto px-4 flex flex-wrap items-center pt-2 md:pt-4 gap-1 md:gap-2 text-xs sm:text-sm md:text-base lg:text-lg mb-4 md:mb-6">
            <a href="#" class="text-[#632085] hover:text-[#52166d] transition font-medium">Home</a>
            <span class="text-gray-400">/</span>
            <a href="#" class="text-[#632085] hover:text-[#52166d] transition font-medium">Shoping Cart</a>

        </nav>
    </section>
    <div class="container mx-auto px-4 py-4 md:py-10">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-start">

            <!-- ════════════════════════════════════════
                     LEFT COLUMN (Forms & Payments) - col-span-8
                    ════════════════════════════════════════ -->
            <div class="lg:col-span-8 flex flex-col gap-6">

                <!-- Card 1: Shipping Address -->
                <section class="bg-white p-4 sm:p-5 md:p-6 border border-gray-100 rounded-lg shadow-sm">
                    <h2 class="text-lg md:text-xl font-bold text-[#0f172a] mb-4 md:mb-6">Shipping Address</h2>

                    <form class="space-y-4 md:space-y-5">

                        <!-- First Name & Last Name -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1 md:mb-1.5">First
                                    Name</label>
                                <input type="text" placeholder="Enter your first name"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 md:px-4 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[#632085] focus:border-transparent transition" />
                            </div>
                            <div>
                                <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1 md:mb-1.5">Last
                                    Name</label>
                                <input type="text" placeholder="Enter your last name"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 md:px-4 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[#632085] focus:border-transparent transition" />
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1 md:mb-1.5">Phone
                                *</label>
                            <input type="tel" placeholder="Enter your Number" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 md:px-4 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[#632085] focus:border-transparent transition" />
                        </div>

                        <!-- City Dropdown -->
                        <div>
                            <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1 md:mb-1.5">City
                                *</label>
                            <div class="relative">
                                <select required
                                    class="w-full appearance-none border border-gray-300 rounded-lg px-3 py-2.5 md:px-4 md:py-3 text-sm md:text-base bg-white text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#632085] focus:border-transparent transition">
                                    <option value="" disabled selected>Select now</option>
                                    <option value="dhaka">Dhaka</option>
                                    <option value="chittagong">Chittagong</option>
                                    <option value="sylhet">Sylhet</option>
                                    <option value="rajshahi">Rajshahi</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 md:px-4 text-gray-500">
                                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Zone Dropdown -->
                        <div>
                            <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1 md:mb-1.5">Select
                                Zone</label>
                            <div class="relative">
                                <select
                                    class="w-full appearance-none border border-gray-300 rounded-lg px-3 py-2.5 md:px-4 md:py-3 text-sm md:text-base bg-white text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#632085] focus:border-transparent transition">
                                    <option value="" disabled selected>Select now</option>
                                    <option value="dhanmondi">Dhanmondi</option>
                                    <option value="gulshan">Gulshan</option>
                                    <option value="banani">Banani</option>
                                    <option value="uttara">Uttara</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 md:px-4 text-gray-500">
                                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Address with Magnifier Search Icon -->
                        <div>
                            <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1 md:mb-1.5">Address
                                *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 md:pl-4 text-gray-400">
                                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input type="text" placeholder="Search and enter address detail" required
                                    class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 md:pl-11 md:pr-4 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[#632085] focus:border-transparent transition" />
                            </div>
                        </div>

                    </form>
                </section>

                <!-- Card 2: Select Payment Method -->
                <section class="bg-white p-4 sm:p-5 md:p-6 border border-gray-100 rounded-lg shadow-sm">
                    <h2 class="text-lg md:text-xl font-bold text-[#0f172a] mb-4 md:mb-6">Select Payment method</h2>

                    <div class="space-y-3 md:space-y-4">

                        <!-- COD option -->
                        <label
                            class="payment-option-label flex items-center justify-between p-3 md:p-4 border border-[#632085] bg-purple-50/20 rounded-xl cursor-pointer transition"
                            data-method="cod">
                            <div class="flex items-center gap-3">
                                <div
                                    class="radio-ring w-4 h-4 md:w-5 md:h-5 rounded-full border-2 border-[#632085] flex items-center justify-center shrink-0">
                                    <div class="radio-dot w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-[#632085]"></div>
                                </div>
                                <span class="text-sm md:text-base font-bold text-[#0f172a]">Cash On Delivery</span>
                            </div>
                            <!-- COD Image -->
                            <img src="assets/images/cod.png" alt="Cash On Delivery"
                                class="w-6 h-6 md:w-8 md:h-8 object-contain shrink-0" />
                        </label>

                        <!-- bKash option -->
                        <label
                            class="payment-option-label flex items-center justify-between p-3 md:p-4 border border-gray-200 hover:border-[#632085] rounded-xl cursor-pointer transition"
                            data-method="bkash">
                            <div class="flex items-center gap-3">
                                <div
                                    class="radio-ring w-4 h-4 md:w-5 md:h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0">
                                    <div class="radio-dot w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-transparent"></div>
                                </div>
                                <span class="text-sm md:text-base font-bold text-[#0f172a]">Pay by Bkash</span>
                            </div>
                            <!-- bKash Image -->
                            <img src="assets/images/bkash.png" alt="Bkash"
                                class="w-6 h-6 md:w-8 md:h-8 object-contain rounded shrink-0" />
                        </label>

                        <!-- Nagad option -->
                        <label
                            class="payment-option-label flex items-center justify-between p-3 md:p-4 border border-gray-200 hover:border-[#632085] rounded-xl cursor-pointer transition"
                            data-method="nagad">
                            <div class="flex items-center gap-3">
                                <div
                                    class="radio-ring w-4 h-4 md:w-5 md:h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0">
                                    <div class="radio-dot w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-transparent"></div>
                                </div>
                                <span class="text-sm md:text-base font-bold text-[#0f172a]">Pay by Nagad</span>
                            </div>
                            <!-- Nagad Image -->
                            <img src="assets/images/nagad.png" alt="Nagad"
                                class="w-6 h-6 md:w-8 md:h-8 object-contain rounded shrink-0" />
                        </label>

                    </div>
                </section>

            </div>

            <!-- ════════════════════════════════════════
                     RIGHT COLUMN (Order Summary) - col-span-4
                    ════════════════════════════════════════ -->
            <aside
                class="lg:col-span-4 bg-white p-4 sm:p-5 md:p-6 border border-gray-100 rounded-lg shadow-sm lg:sticky lg:top-24">

                <h3 class="text-lg md:text-xl font-bold text-[#0f172a] mb-4 md:mb-6">Order Summary</h3>

                <!-- Subtotal Row -->
                <div class="flex justify-between items-center py-3 md:py-4 border-b border-gray-100">
                    <span class="text-sm md:text-base text-gray-600">Subtotal</span>
                    <span class="text-sm md:text-base font-bold text-gray-900">BDT 500.00</span>
                </div>

                <!-- Shipping Row -->
                <div class="flex justify-between items-center py-3 md:py-4 border-b border-gray-100">
                    <span class="text-sm md:text-base text-gray-600">Shipping (First Class)</span>
                    <span class="text-sm md:text-base font-bold text-gray-900">BDT 500.00</span>
                </div>

                <!-- Tax Row -->
                <div class="flex justify-between items-center py-3 md:py-4 border-b border-gray-100">
                    <span class="text-sm md:text-base text-gray-600">Tax</span>
                    <span class="text-sm md:text-base font-bold text-gray-900">BDT 52.72</span>
                </div>

                <!-- Total Row (Blue text) -->
                <div class="flex justify-between items-center py-4 md:py-5">
                    <span class="text-base md:text-lg font-medium text-gray-500">Total</span>
                    <span class="text-xl md:text-2xl font-extrabold text-[#1147aa]">BDT 3000.00</span>
                </div>

                <!-- Place Order Button (Purple with padlock icon) -->
                <div class="mt-2 md:mt-4">
                    <button
                        class="w-full bg-[#632085] hover:bg-[#52166d] transition text-white font-bold py-3 md:py-4 px-4 md:px-6 rounded-xl flex items-center justify-center gap-2 text-sm md:text-base shadow-md focus:outline-none">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2.2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Place Order
                    </button>
                </div>

                <!-- Secure Info text -->
                <p class="text-center text-xs md:text-sm text-gray-500 mt-4 leading-normal">
                    Your payment information is encrypted and secure.
                </p>

            </aside>

        </div>
    </div>
@endsection


@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const options = document.querySelectorAll(".payment-option-label");

            options.forEach(option => {
                option.addEventListener("click", function () {
                    // Reset all options to unselected
                    options.forEach(opt => {
                        opt.classList.remove("border-[#632085]", "bg-purple-50/20");
                        opt.classList.add("border-gray-200");

                        const ring = opt.querySelector(".radio-ring");
                        const dot = opt.querySelector(".radio-dot");

                        ring.classList.remove("border-[#632085]");
                        ring.classList.add("border-gray-300");

                        dot.classList.remove("bg-[#632085]");
                        dot.classList.add("bg-transparent");
                    });

                    // Select clicked option
                    this.classList.remove("border-gray-200");
                    this.classList.add("border-[#632085]", "bg-purple-50/20");

                    const activeRing = this.querySelector(".radio-ring");
                    const activeDot = this.querySelector(".radio-dot");

                    activeRing.classList.remove("border-gray-300");
                    activeRing.classList.add("border-[#632085]");

                    activeDot.classList.remove("bg-transparent");
                    activeDot.classList.add("bg-[#632085]");
                });
            });
        });
    </script>
@endpush
