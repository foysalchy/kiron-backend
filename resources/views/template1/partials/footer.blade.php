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
                        <div class="bg-[#FF6A00] w-10 h-10 flex items-center justify-center rounded-lg">
                            <span class="text-white text-xl font-semibold">O</span>
                        </div>
                        <span class="text-xl font-bold tracking-tight">OrenMart</span>
                    </div>
                    <p class="text-gray-400 text-[16px] leading-relaxed mb-6">
                        Your trusted partner for automotive accessories and car care products. Quality products at
                        affordable prices.
                    </p>
                    <ul class="space-y-3 text-[16px]">
                        <li class="flex items-start gap-3 text-gray-400">
                            <i class="fas fa-map-marker-alt text-[#FF6A00] mt-1"></i>
                            <span>123 Main Street, Dhaka, Bangladesh</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="fas fa-phone-alt text-[#FF6A00]"></i>
                            <span>+880 1234-567890</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-400">
                            <i class="fas fa-envelope text-[#FF6A00]"></i>
                            <span>support@orenmart.com</span>
                        </li>
                    </ul>
                </div>

                <!-- Column 2: Quick Links -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Quick Links</h4>
                    <ul class="space-y-3 text-[16px]">
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
                        <li><a href="{{ url('/apply-seller') }}"
                                class="text-gray-400 hover:text-[#FF6A00] transition-colors">Become a Seller</a></li>
                    </ul>
                </div>

                <!-- Column 3: Categories -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Categories</h4>
                    <ul class="space-y-3 text-[16px]">
                        <li><a href="#" class="text-gray-400 hover:text-[#FF6A00] transition-colors">Car
                                Interior</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-[#FF6A00] transition-colors">Car
                                Exterior</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-[#FF6A00] transition-colors">Electronics</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-[#FF6A00] transition-colors">Oil &
                                Care</a></li>
                        <li><a href="#"
                                class="text-gray-400 hover:text-[#FF6A00] transition-colors">Performance</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-[#FF6A00] transition-colors">Safety</a>
                        </li>
                    </ul>
                </div>

                <!-- Column 4: Newsletter -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Newsletter</h4>
                    <p class="text-gray-400 text-[16px] mb-6">Subscribe to get updates on new products and exclusive
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
                    <p>© 2024 OrenMart. All rights reserved.</p>
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
