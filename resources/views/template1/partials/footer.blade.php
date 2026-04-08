<!-- FOOTER -->
<footer class="text-white pt-12">

    <!-- 1. Top Features Row -->
    <div class="bg-[#1A2937] p-6">
        <div class="container mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#FF6A00] rounded-full flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/template1/frontend/truck.svg') }}" alt="truck"
                            class="brightness-0 invert">
                    </div>
                    <div>
                        <h5 class="font-bold text-[16px]">Free Shipping</h5>
                        <p class="text-gray-400 text-sm">On orders over ৳1000</p>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#FF6A00] rounded-full flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/template1/frontend/undo.svg') }}" alt="undo"
                            class="brightness-0 invert">
                    </div>
                    <div>
                        <h5 class="font-bold text-[16px]">Easy Returns</h5>
                        <p class="text-gray-400 text-sm">7 days return policy</p>
                    </div>
                </div>
                <!-- Feature 3 -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#FF6A00] rounded-full flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/template1/frontend/secure.svg') }}" alt="secure"
                            class="brightness-0 invert">
                    </div>
                    <div>
                        <h5 class="font-bold text-[16px]">Secure Payment</h5>
                        <p class="text-gray-400 text-sm">100% secure checkout</p>
                    </div>
                </div>
                <!-- Feature 4 -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#FF6A00] rounded-full flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/template1/frontend/phone.svg') }}" alt="phone"
                            class="brightness-0 invert">
                    </div>
                    <div>
                        <h5 class="font-bold text-[16px]">24/7 Support</h5>
                        <p class="text-gray-400 text-sm">Dedicated support</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-900">
        <!-- 2. Main Footer Content -->
        <div class="container mx-auto px-4 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">

                <!-- Column 1: Brand Info -->
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        @if($setup && $setup->logo)
                            <img src="{{ $setup->logo_url }}" alt="{{ $setup->shop_name }}" class="h-10 w-auto object-contain">
                        @else
                            <div class="w-10 h-10 flex items-center justify-center rounded-lg">
                                <span class="text-white text-xl font-semibold">{{ substr($setup->shop_name ?? 'O', 0, 1) }}</span>
                            </div>
                        @endif
                        <span class="text-xl font-bold tracking-tight">{{ $setup->shop_name ?? 'OrenMart' }}</span>
                    </div>
                       <p class="text-gray-400 text-[16px] leading-relaxed mb-6">
                        {{ $setup->description ?? 'Your trusted partner for automotive accessories and car care products.' }}
                    </p>
                    <ul class="space-y-3 text-md">
                        <li class="flex items-start gap-3 text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin h-4 w-4 text-orange-500"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span>{{ $setup->corporate_address ?? 'Dhaka, Bangladesh' }}</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone h-4 w-4 text-orange-500"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            {{ $setup->phone }}
                        </li>
                        <li class="flex items-center gap-3 text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail h-4 w-4 text-orange-500"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                            {{ $setup->email }}
                        </li>
                    </ul>
                </div>

                <!-- Column 2: Quick Links -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Quick Links</h4>
                    <ul class="space-y-3 text-md">
                        <li><a href="{{ url('/about') }}"
                                class="text-gray-400 hover:text-[#FF6A00] transition-colors">About Us</a></li>
                        <li><a href="{{ url('/contact') }}"
                                class="text-gray-400 hover:text-[#FF6A00] transition-colors">Contact Us</a></li>
                        <li><a href="{{ url('/product-track') }}"
                                class="text-gray-400 hover:text-[#FF6A00] transition-colors">Track Order</a></li>
                        <li><a href="{{ url('/support') }}"
                                class="text-gray-400 hover:text-[#FF6A00] transition-colors">Help & Support</a></li>
                        <li><a href="{{ url('/blogs') }}"
                                class="text-gray-400 hover:text-[#FF6A00] transition-colors">Blog</a></li>

                    </ul>
                </div>

                <!-- Column 3: Categories -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Categories</h4>
                    <ul class="space-y-3 text-md">
                        @foreach($headerCategories as $cat)
                            <li>
                                <a href="{{ url('/category/' . $cat->slug) }}"
                                class="text-gray-400 hover:text-[#FF6A00] transition-colors">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Column 4: Newsletter -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Newsletter</h4>
                    <p class="text-gray-400 text-md mb-6">Subscribe to get updates on new products and exclusive
                        offers.</p>
                    <div class="flex mb-6">
                        <input type="email" placeholder="Enter your email"
                            class="bg-[#1A222F] border border-gray-700 text-white px-4 py-2.5 rounded-l-md w-full focus:outline-none focus:border-[#FF6A00]">
                        <button
                            class="bg-[#FF6A00] hover:bg-orange-600 px-5 py-2.5 rounded-r-md font-semibold transition-colors">
                            Subscribe
                        </button>
                    </div>
                    <div class="flex gap-4">
                        <a href="#" class="text-gray-400 hover:text-[#FF6A00] text-lg"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-[#FF6A00] text-lg"><i
                                class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-[#FF6A00] text-lg"><i
                                class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-[#FF6A00] text-lg"><i
                                class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Bottom Bar -->
        <div class="border-t border-gray-800 py-6">
            <div class="container mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex flex-wrap justify-center gap-6 text-[14px] text-gray-400">
                    <p>© {{ date('Y') }} {{ $setup->shop_name ?? 'OrenMart' }}. All rights reserved.</p>
                    <a href="{{ url('/privacy') }}" class="hover:text-white">Privacy Policy</a>
                    <a href="{{ url('/terms') }}" class="hover:text-white">Terms of Service</a>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-[14px] text-gray-400">We Accept:</span>
                    <div class="flex gap-2">
                        <!-- Placeholder boxes for payment icons as seen in image -->
                        <div class="bg-white px-2 py-1 rounded text-[#1D2128] h-6 flex items-center"><img
                                src="{{ asset('images/template1/frontend/card.svg') }}" alt="card"></div>
                        <div class="bg-white px-2 py-1 rounded text-gray-700 text-[12px] h-6 flex items-center">bKash
                        </div>
                        <div class="bg-white px-2 py-1 rounded text-gray-700 text-[12px] h-6 flex items-center">Nagad
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</footer>
