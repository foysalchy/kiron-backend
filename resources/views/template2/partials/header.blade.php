<header class="w-full bg-white sticky top-0 z-50 shadow-sm">
    <!-- 1. Main Header -->
    <div class="container mx-auto px-4 py-3 md:py-4">
        <div class="flex items-center justify-between gap-4 lg:gap-8">

            <!-- Mobile Menu Toggle (Visible only on Mobile) -->
            <button onclick="toggleMobileMenu()" class="md:hidden text-[#016738] text-2xl focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex-shrink-0">
                @if ($setup && $setup->logo)
                    <img src="{{ $setup->logo_url }}" alt="{{ $setup->shop_name }}" class="h-8 sm:h-10 md:h-14 w-auto">
                @else
                    <span class="text-xl md:text-2xl font-bold text-[#016738]">খাঁটি ভাই</span>
                @endif
            </a>

            <!-- Segmented Search Bar (Hidden on Mobile, Visible on Desktop) -->
            <form action="{{ route('shop.index') }}" method="GET" class="hidden md:flex flex-1 max-w-2xl">
                <div class="flex w-full border border-[#016738] rounded-sm overflow-hidden bg-white">
                    <!-- Category Dropdown -->
                    <div class="relative flex-shrink-0 border-r border-[#016738] min-w-[130px]">
                        <select name="category"
                            class="w-full h-full pl-6 py-2 text-base text-[#016738] font-bold bg-transparent outline-none appearance-none cursor-pointer">
                            <option value="">সব দেখুন</option>
                            @foreach ($headerCategories as $cat)
                                <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-base text-[#016738]"></i>
                        </div>
                    </div>
                    <!-- Input -->
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="প্রোডাক্ট খুঁজুন..."
                        class="flex-1 px-4 py-2 text-base text-black outline-none placeholder:text-gray-500">
                    <!-- Search Button -->
                    <button type="submit"
                        class="primary-bg text-primary px-8 py-2 text-lg font-bold hover:bg-opacity-95 transition-colors">খুঁজুন</button>
                </div>
            </form>

            <!-- Right Side Actions (Cart & Account) -->
            <div class="flex items-center gap-3 sm:gap-4 lg:gap-6">
                <!-- Cart Icon -->
                <button onclick="toggleCartDrawer()" class="relative group outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 md:h-8 md:w-8 text-[#016738]" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span
                        class="cart-count-nav absolute -top-1 -right-1 bg-black text-primary text-[10px] font-bold h-4 w-4 md:h-5 md:w-5 flex items-center justify-center rounded-full border-2 border-white">
                        {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}
                    </span>
                </button>

                <!-- Account Icon -->
                <div class="relative group hidden md:block" id="desktop-account-wrapper">
                    @auth('customer')
                        <!-- Logged In User Trigger -->
                        <button onclick="toggleDesktopAccount()"
                            class="flex items-center gap-2 text-[#016738] outline-none cursor-pointer select-none">
                            <div
                                class="w-8 h-8 md:w-9 md:h-9 rounded-full primary-bg text-primary flex items-center justify-center font-bold border-2 border-white shadow-sm">
                                {{ substr(auth('customer')->user()->name, 0, 1) }}
                            </div>
                            <div class="hidden lg:block text-left">
                                <p class="text-sm font-bold truncate max-w-[100px]">{{ auth('customer')->user()->name }}</p>
                            </div>
                            <i class="fas fa-chevron-down text-[10px] ml-1 transition-transform duration-300"
                                id="account-chevron"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="desktop-account-menu"
                            class="hidden absolute right-0 mt-3 w-52 bg-white border border-gray-100 rounded-lg shadow-xl z-50 py-2">
                            <div class="px-4 py-2 border-b border-gray-50 mb-2">
                                <p class="text-xs text-gray-400">লগইন করা আছে</p>
                                <p class="text-sm font-bold text-gray-800 truncate">{{ auth('customer')->user()->email }}
                                </p>
                            </div>

                            <a href="{{ route('user.dashboard') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-[#016738] transition-colors">
                                <i class="fas fa-th-large w-4 text-gray-400"></i> ড্যাশবোর্ড
                            </a>
                            <a href="{{ route('user.dashboard') }}?section=orders"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-[#016738] transition-colors">
                                <i class="fas fa-box w-4 text-gray-400"></i> আমার অর্ডারসমূহ
                            </a>
                            <a href="{{ route('user.profile') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-[#016738] transition-colors">
                                <i class="fas fa-user-edit w-4 text-gray-400"></i> প্রোফাইল আপডেট
                            </a>

                            <div class="border-t border-gray-50 mt-2 pt-1">
                                <form action="{{ route('user.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-red-500 hover:bg-red-50 transition-colors">
                                        <i class="fas fa-sign-out-alt w-4"></i> লগআউট করুন
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest User Icon -->
                        <a href="{{ route('user.login') }}" class="flex items-center gap-2 text-[#016738] group/login">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 group-hover/login:scale-110 transition-transform" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>

                        </a>
                    @endauth
                </div>

                <!-- Call Button (Desktop Only) -->
                <a href="tel:{{ $setup->phone ?? ''}}"
                    class="hidden lg:flex items-center gap-2 primary-bg text-primary px-4 py-2.5 rounded-md font-medium">
                    <i class="fas fa-phone-alt text-sm"></i>
                    <span>কল করুন</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Search Bar (Visible only on Mobile, matches your image) -->
    <div class="md:hidden px-4 pb-4">
        <form action="{{ route('shop.index') }}" method="GET"
            class="flex border border-[#016738] rounded-sm overflow-hidden bg-white">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="প্রোডাক্ট খুঁজুন.."
                class="flex-1 px-3 py-2 text-sm outline-none">
            <button type="submit" class="primary-bg text-primary px-4 py-2 font-bold text-sm transition-colors">
                খুঁজুন
            </button>
        </form>
    </div>

    <!-- 2. Desktop Bottom Nav (Hidden on Mobile) -->
    <nav class="primary-bg hidden md:block">
        <div class="container mx-auto">
            <div
                class="flex items-center justify-center text-primary py-3 text-lg overflow-x-auto no-scrollbar flex-nowrap">
                <a href="{{ route('home') }}"
                    class="px-4 hover:text-yellow-400 transition-colors font-medium border-r border-white/30 last:border-0 whitespace-nowrap flex-shrink-0">হোমপেজ</a>
                <a href="{{ route('flash.sale') }}"
                    class="px-4 hover:text-yellow-400 transition-colors font-medium border-r border-white/30 last:border-0">অফার</a>
                @foreach ($headerCategories->take(9) as $cat)
                    <a href="{{ route('category.products', $cat->slug) }}"
                        class="px-4 hover:text-yellow-400 transition-colors font-medium border-r border-white/30 last:border-0">{{ $cat->name }}</a>
                @endforeach
            </div>
        </div>
    </nav>
</header>

<!-- Mobile Menu Drawer -->
<div id="mobile-menu" class="hidden lg:hidden fixed inset-0 z-[3000] flex">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="toggleMobileMenu()"></div>

    <!-- Drawer -->
    <div class="relative w-72 max-w-[85vw] bg-white h-full overflow-y-auto flex flex-col">

        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-5 bg-white border-b border-gray-50">
            @if ($setup && $setup->logo)
                <img src="{{ $setup->logo_url }}" alt="{{ $setup->shop_name }}" class="h-8 w-auto">
            @else
                <span class="text-xl font-bold text-[#016738]">খাঁটি ভাই</span>
            @endif
            <button onclick="toggleMobileMenu()" class="text-[#016738] text-2xl focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- User Brief Info (লগইন থাকলে নাম দেখাবে) -->
        @auth('customer')
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full primary-bg text-primary flex items-center justify-center font-bold">
                    {{ substr(auth('customer')->user()->name, 0, 1) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-bold text-gray-800 truncate">{{ auth('customer')->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth('customer')->user()->email }}</p>
                </div>
            </div>
        @endauth

        <!-- Navigation Links -->
        <nav class="flex flex-col p-6 gap-5">
            <a href="{{ route('home') }}"
                class="text-[17px] font-bold text-[#016738] hover:opacity-80 transition-opacity">হোমপেজ</a>
            <a href="{{ route('flash.sale') }}"
                class="text-[17px] font-bold text-[#016738] hover:opacity-80 transition-opacity">অফার</a>

            @foreach ($headerCategories as $cat)
                <a href="{{ route('category.products', $cat->slug) }}"
                    class="text-[17px] font-bold text-[#016738] hover:opacity-80 transition-opacity">
                    {{ $cat->name }}
                </a>
            @endforeach
        </nav>

        <!-- Bottom Actions -->
        <div class="mt-auto p-5 flex flex-col gap-3 mb-6">

            @auth('customer')
                <!-- ড্যাশবোর্ড বাটন (লগইন থাকলে) -->
                <a href="{{ route('user.dashboard') }}"
                    class="flex items-center justify-center gap-3 primary-bg text-primary py-3 rounded shadow-sm font-bold text-base hover:bg-opacity-95 transition-all">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>আমার ড্যাশবোর্ড</span>
                </a>

                <!-- লগআউট বাটন (লগইন থাকলে) -->
                <form action="{{ route('user.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-center text-red-500 font-bold text-sm py-2 hover:underline">
                        লগআউট করুন
                    </button>
                </form>
            @else
                <!-- লগইন / রেজিস্টার বাটন (লগইন না থাকলে) -->
                <a href="{{ route('user.login') }}"
                    class="flex items-center justify-center gap-3 primary-bg text-primary py-3 rounded shadow-sm font-bold text-base hover:bg-opacity-95 transition-all">
                    <i class="fas fa-user-circle text-xl"></i>
                    <span>লগইন / রেজিস্টার</span>
                </a>
            @endauth

            <!-- কল করুন বাটন (সব সময় থাকবে) -->
            <a href="tel:{{ $setup->phone ?? ''}}"
                class="flex items-center justify-center gap-3 primary-bg text-primary py-3 rounded shadow-sm font-bold text-base hover:bg-opacity-95 transition-all">
                <i class="fas fa-phone-alt"></i>
                <span>কল করুন</span>
            </a>
        </div>
    </div>
</div>
<!-- Cart Drawer Overlay -->
<div id="cart-overlay" onclick="toggleCartDrawer()"
    class="fixed inset-0 bg-black/50 z-[1100] hidden transition-opacity duration-300"></div>

<!-- Cart Drawer Panel -->
<div id="cart-drawer"
    class="fixed top-0 right-0 h-full w-[350px] max-w-[95vw] sm:max-w-[90vw] bg-white z-[1200] shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col">

    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b">
        <h2 class="text-lg font-bold text-gray-800">Shopping Cart</h2>
        <button onclick="toggleCartDrawer()" class="text-gray-500 hover:text-red-500 text-2xl">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Products List -->
    <div class="flex-1 overflow-y-auto p-4">
        @php $cartItems = \Gloudemans\Shoppingcart\Facades\Cart::content(); @endphp

        @forelse($cartItems as $item)
            <div class="flex gap-3 mb-4 pb-4 border-b border-gray-100 last:border-0">
                <div class="w-20 h-20 flex-shrink-0 bg-gray-50 rounded overflow-hidden">
                    <img src="{{ $item->options->image ?? asset('images/no-image.png') }}" alt="{{ $item->name }}"
                        class="w-full h-full object-contain">
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-gray-800 leading-tight">{{ $item->name }}</h3>
                    <p class="text-xs text-gray-500 mt-1">পরিমাণ: {{ $item->options->variant ?? 'N/A' }}</p>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-sm font-bold text-[#016738]">{{ $item->qty }} ×
                            {{ number_format($item->price, 0) }}৳</span>
                        <a href="{{ route('cart.remove', $item->rowId) }}" class="text-gray-400 hover:text-red-500">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="h-full flex flex-col items-center justify-center text-gray-400">
                <i class="fas fa-shopping-basket text-5xl mb-3"></i>
                <p>আপনার কার্ট খালি</p>
            </div>
        @endforelse
    </div>

    <!-- Footer -->
    @if (count($cartItems) > 0)
        <div class="p-4 border-t bg-gray-50">
            <div class="flex justify-between items-center mb-4">
                <span class="text-lg font-bold text-gray-700">SUBTOTAL:</span>
                <span
                    class="text-lg font-bold text-gray-900">{{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal() }}৳</span>
            </div>

            <div class="space-y-3">
                <a href="{{ route('cart.index') }}"
                    class="block w-full text-center primary-bg text-primary py-3 rounded font-bold uppercase hover:bg-opacity-90 transition-colors">
                    VIEW CART
                </a>
                <a href="{{ route('checkout.index') }}"
                    class="block w-full text-center bg-black text-primary py-3 rounded font-bold uppercase hover:bg-opacity-90 transition-colors">
                    CHECKOUT
                </a>
            </div>
        </div>
    @endif
</div>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }

    function toggleCartDrawer() {
        const drawer = document.getElementById('cart-drawer');
        const overlay = document.getElementById('cart-overlay');

        if (drawer.classList.contains('translate-x-full')) {
            // Open
            drawer.classList.remove('translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // স্ক্রল বন্ধ করবে
        } else {
            // Close
            drawer.classList.add('translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = ''; // স্ক্রল চালু করবে
        }
    }
    // Toggle Desktop Account Dropdown
    function toggleDesktopAccount() {
        const menu = document.getElementById('desktop-account-menu');
        const chevron = document.getElementById('account-chevron');
        menu.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }

    // Close dropdown when clicking outside
    window.addEventListener('click', function(e) {
        const wrapper = document.getElementById('desktop-account-wrapper');
        const menu = document.getElementById('desktop-account-menu');
        const chevron = document.getElementById('account-chevron');

        if (wrapper && !wrapper.contains(e.target)) {
            if (menu) menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    });
</script>
