<header class="sticky top-0 z-50 w-full bg-white border-b border-gray-100">
    <div class="container mx-auto px-4 md:px-10 h-20 flex items-center justify-between">
        <!-- Logo Section -->
        <div class="flex-shrink-0">
            <a href="{{ route('saas.index') }}" class="flex items-center gap-2"
                aria-label="{{ $setup->shop_name ?? 'Home' }}">
                @if ($setup && $setup->logo_url)
                    <img src="{{ $setup->logo_url }}" alt="{{ $setup->shop_name }}" class="h-8 md:h-9 w-auto"
                        fetchpriority="high" loading="eager"
                        onerror="this.onerror=null; this.src='{{ asset('images/saas/Shopify_Logo.png') }}';">
                @else
                    @if (file_exists(public_path('images/saas/Shopify_Logo.png')))
                        <img src="{{ asset('images/saas/Shopify_Logo.png') }}" alt="Default Logo"
                            class="h-8 md:h-9 w-auto" fetchpriority="high">
                    @else
                        <span class="text-xl font-bold text-gray-900">{{ $setup->shop_name ?? 'Bhaiya Digital' }}</span>
                    @endif
                @endif
            </a>
        </div>

        <!-- Navigation Links (Desktop) -->
        <nav class="hidden lg:flex items-center space-x-8">
            <a href="{{ route('saas.index') }}" class="text-[#5c46e5] font-semibold text-lg">হোম</a>
            <a href="#" class="text-gray-700 hover:text-[#5c46e5] font-semibold text-lg transition">ফিচারসমূহ</a>
            <a href="#"
                class="text-gray-700 hover:text-[#5c46e5] font-semibold text-lg transition">ইন্টিগ্রেশন</a>
            <a href="#" class="text-gray-700 hover:text-[#5c46e5] font-semibold text-lg transition">প্রাইসিং</a>
            <a href="#" class="text-gray-700 hover:text-[#5c46e5] font-semibold text-lg transition">যোগাযোগ</a>
        </nav>

        <!-- Right Side Buttons -->
        <div class="flex items-center space-x-3 md:space-x-6">
            <a href="https://app.dorja.io" class="hidden md:block text-gray-900 font-bold text-lg hover:text-[#5c46e5]">
                লগইন
            </a>
            <!-- CTA Button (Responsive Padding & Font) -->
            <a href="https://app.dorja.io/register"
                class="bg-[#5c46e5] text-white px-4 py-2.5 md:px-6 md:py-3 rounded-xl font-bold text-xs md:text-lg hover:bg-[#4a38b8] transition shadow-sm whitespace-nowrap">
                ফ্রি ট্রায়াল <span class="hidden sm:inline">শুরু করুন</span>
            </a>

            <!-- Mobile Menu Toggle Button -->
            <button id="menu-toggle" class="lg:hidden text-gray-700 text-2xl p-1">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
        </div>
    </div>

    <!-- MOBILE MENU SIDEBAR (Hidden by default) -->
    <div id="mobile-menu" class="fixed inset-0 z-[60] lg:hidden translate-x-full">
        <!-- Overlay -->
        <div id="menu-overlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        <!-- Sidebar Content -->
        <div class="absolute right-0 top-0 h-full w-[280px] bg-white shadow-2xl p-6">
            <div class="flex items-center justify-between mb-8">
                <span class="font-bold text-xl">মেনু</span>
                <button id="menu-close" class="text-gray-700 text-2xl">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <nav class="flex flex-col space-y-5">
                <a href="#" class="text-[#5c46e5] font-bold text-lg">হোম</a>
                <a href="#" class="text-gray-700 font-semibold text-lg border-b border-gray-50 pb-2">ফিচারসমূহ</a>
                <a href="#"
                    class="text-gray-700 font-semibold text-lg border-b border-gray-50 pb-2">ইন্টিগ্রেশন</a>
                <a href="#" class="text-gray-700 font-semibold text-lg border-b border-gray-50 pb-2">প্রাইসিং</a>
                <a href="#" class="text-gray-700 font-semibold text-lg border-b border-gray-50 pb-2">যোগাযোগ</a>
                <div class="pt-4">
                    <a href="#"
                        class="block text-center bg-gray-100 text-gray-900 py-3 rounded-xl font-bold">লগইন</a>
                </div>
            </nav>
        </div>
    </div>
</header>
