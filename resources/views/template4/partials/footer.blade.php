    <!-- FOOTER SECTION -->
    <footer class="bg-[#3b143c] text-white pt-20 pb-10" role="contentinfo">
        <div class="container mx-auto p-4">
            <!-- Top Part: Logo & Menus -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-12 mb-16">
                <!-- Column 1: Logo & Newsletter (Takes 2 parts space) -->
                <div class="lg:col-span-2 space-y-8">
                    <div>
                        <a href="{{ route('home') }}" aria-label="Little Joy Home">
                            <img src="{{ $setup->logo_url ?? asset('images/babyshop/images/babylogo.png') }}"
                                alt="Little Joy Baby Shop Logo" height="" width="" class="h-20">
                        </a>
                    </div>
                    <p class="text-base font-light leading-relaxed max-w-[280px]">
                        No need to worry, we'll help you make sense of it all...
                    </p>

                    <!-- Newsletter Box (Same as Image) -->
                    <div class="relative max-w-[320px]">
                        <input type="email" placeholder="Email Address" aria-label="Email address for newsletter"
                            class="w-full bg-white text-gray-800 py-3 px-5 rounded-lg focus:outline-none placeholder:text-gray-400 font-medium" />
                        <button type="submit"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-[#3b143c] hover:scale-110 transition-transform"
                            aria-label="Subscribe">
                            <i class="fa-solid fa-paper-plane text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Column 2: SOLUTIONS -->
                <div class="lg:col-start-3">
                    <h3 class="text-lg font-bold uppercase tracking-widest mb-8">
                        Solutions
                    </h3>
                    <ul class="space-y-4 text-white/90 text-base">
                        <li><a href="#" class="hover:underline">Careers</a></li>
                        <li><a href="#" class="hover:underline">Resources</a></li>
                        <li><a href="#" class="hover:underline">Finance</a></li>
                        <li><a href="#" class="hover:underline">Management</a></li>
                        <li><a href="#" class="hover:underline">Workflow</a></li>
                    </ul>
                </div>

                <!-- Column 3: ABOUT US -->
                <div>
                    <h3 class="text-lg font-bold uppercase tracking-widest mb-8">
                        About Us
                    </h3>
                    <ul class="space-y-4 text-white/90 text-base">
                        <li><a href="#" class="hover:underline">What We Offer</a></li>
                        <li><a href="#" class="hover:underline">Solutions</a></li>
                        <li><a href="#" class="hover:underline">Careers</a></li>
                        <li><a href="#" class="hover:underline">Pricing</a></li>
                        <li><a href="#" class="hover:underline">Features</a></li>
                    </ul>
                </div>

                <!-- Column 4: PERSONAL -->
                <div>
                    <h3 class="text-lg font-bold uppercase tracking-widest mb-8">
                        Personal
                    </h3>
                    <ul class="space-y-4 text-white/90 text-base">
                        <li><a href="#" class="hover:underline">Features</a></li>
                        <li><a href="#" class="hover:underline">Profile</a></li>
                        <li><a href="#" class="hover:underline">Payments</a></li>
                        <li><a href="#" class="hover:underline">Accounts</a></li>
                    </ul>
                </div>

                <!-- Column 5: SOCIAL -->
                <div>
                    <h3 class="text-lg font-bold uppercase tracking-widest mb-8">
                        Social
                    </h3>
                    <ul class="space-y-4 text-white/90 text-base">
                        <li><a href="#" class="hover:underline">Twitter</a></li>
                        <li><a href="#" class="hover:underline">Facebook</a></li>
                        <li><a href="#" class="hover:underline">Linkedin</a></li>
                        <li><a href="#" class="hover:underline">Instagram</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Part: Divider & Copyright -->
            <div class="border-t border-white/40 pt-8 mt-10">
                <p class="text-center text-white/90 text-base tracking-wide">
                    @ 2025. All Rights Reserved
                </p>
            </div>
        </div>
    </footer>
