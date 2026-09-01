<header class="w-full header-custom-bg fixed top-0 left-0 right-0 z-50 shadow-sm">
    <!-- 1. Main Header -->
    <div class="container mx-auto px-4 py-3 md:py-4">
        <div class="flex items-center justify-between gap-4 lg:gap-8">

            <!-- Mobile Menu Toggle (Visible only on Mobile) -->
            <button onclick="toggleMobileMenu()"
                class="md:hidden text-header text-2xl focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex-shrink-0">
                @if ($setup && $setup->logo)
                    <img src="{{ $setup->logo_url ?? asset('images/logo.jpeg') }}" alt="{{ $setup->shop_name }}" class="h-8 sm:h-10 md:h-14 w-auto">
                @else
                    <span class="text-xl md:text-2xl font-bold text-header">খাঁটি ভাই</span>
                @endif
            </a>

            <!-- Segmented Search Bar with Suggestions -->
            <form action="{{ route('shop.index') }}" method="GET" class="hidden md:flex flex-1 max-w-2xl relative"
                id="header-search-container">
                <div
                    class="flex w-full border border-[var(--primary-color)] rounded-sm overflow-hidden bg-white z-30 relative">
                    <!-- Category Dropdown -->
                    <div class="relative flex-shrink-0 border-r border-[var(--primary-color)] w-[130px] max-w-[200px]">
                        <select name="category" id="header-category-select"
                            class="w-full h-full pl-3 pr-8 py-2 text-sm md:text-base text-header font-bold bg-transparent outline-none appearance-none cursor-pointer">
                            <option value="">All</option>
                            @foreach ($headerCategories as $cat)
                                <option value="{{ $cat->slug }}"
                                    {{ request('category') == $cat->slug ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-2 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-[10px] text-header"></i>
                        </div>
                    </div>
                    <!-- Input -->
                    <input type="text" name="search" id="header-search-input" autocomplete="off"
                        value="{{ request('search') }}" placeholder="Find all product..."
                        class="flex-1 px-4 py-2 text-base text-black outline-none placeholder:text-gray-500">
                    <!-- Search Button -->
                    <button type="submit"
                        class="primary-bg text-primary px-8 py-2 text-lg font-bold hover:bg-opacity-95 transition-colors">খুঁজুন</button>
                </div>

                <!-- Search Suggestions Dropdown -->
                <div id="search-suggestions"
                    class="hidden absolute top-full left-0 w-full bg-white mt-1 rounded-b-xl shadow-2xl border border-gray-100 z-20 overflow-hidden pt-2">

                    <div id="suggestion-content">
                        <div class="pb-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">জনপ্রিয়
                                সার্চ</p>
                            @forelse($popularSearches ?? [] as $item)
                                <a href="{{ route('shop.index', ['search' => $item->keyword]) }}"
                                    class="flex items-center justify-between px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-history text-gray-300 text-xs"></i>
                                        <span>{{ $item->keyword }}</span>
                                    </div>
                                    <i class="fa-solid fa-arrow-trend-up text-[10px] text-gray-200"></i>
                                </a>
                            @empty
                                <p class="px-5 py-2 text-xs text-gray-400 italic">Search history not found!!!</p>
                            @endforelse
                        </div>
                        <div class="border-t border-gray-50 pt-2 pb-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Trending products</p>
                            @foreach ($relatedProducts ?? [] as $p)
                                <a href="{{ route('shop.index', ['search' => $p->title]) }}"
                                    class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <img src="{{ $p->thumbnail_url }}"
                                        class="w-6 h-6 rounded object-cover border border-gray-100">
                                    <span class="truncate">{{ $p->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- লাইভ সার্চ রেজাল্ট (টাইপ করলে এখানে দেখাবে) -->
                    <div id="live-search-results" class="hidden py-2 border-t border-gray-50"></div>
                </div>
            </form>

            <!-- Right Side Actions (Cart & Account) -->
            <div class="flex items-center gap-3 sm:gap-4 lg:gap-6">
                <!-- Cart Icon -->
                <button onclick="toggleCartDrawer()" class="relative group outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 md:h-8 md:w-8 text-header"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            class="flex items-center gap-2 text-header outline-none cursor-pointer select-none">
                            <div
                                class="w-8 h-8 md:w-9 md:h-9 rounded-full primary-bg text-primary flex items-center justify-center font-bold border-2 border-white shadow-sm">
                                {{ substr(auth('customer')->user()->name, 0, 1) }}
                            </div>
                            <div class="hidden lg:block text-left">
                                <p class="text-sm font-bold truncate max-w-[100px]">{{ auth('customer')->user()->name }}
                                </p>
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
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-header transition-colors">
                                <i class="fas fa-th-large w-4 text-gray-400"></i> ড্যাশবোর্ড
                            </a>
                            <a href="{{ route('user.dashboard') }}?section=orders"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-header transition-colors">
                                <i class="fas fa-box w-4 text-gray-400"></i> আমার অর্ডারসমূহ
                            </a>
                            <a href="{{ route('user.profile') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-header transition-colors">
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
                        <a href="{{ route('user.login') }}"
                            class="flex items-center gap-2 text-header group/login">
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
                <a href="tel:{{ $setup->phone ?? '' }}"
                    class="hidden lg:flex items-center gap-2 primary-bg text-primary px-4 py-2.5 rounded-md font-medium">
                    <i class="fas fa-phone-alt text-sm"></i>
                    <span>কল করুন</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Search Bar (Updated with Segment and Suggestions) -->
    <div class="md:hidden px-4 pb-4 relative" id="mobile-search-container">
        <form action="{{ route('shop.index') }}" method="GET"
            class="flex border border-[var(--primary-color)] rounded-sm overflow-hidden bg-white">
            <!-- Category Segment for Mobile -->
            <div class="relative flex-shrink-0 border-r border-[var(--primary-color)] bg-gray-50 w-[100px] flex items-center">
                <select name="category" id="mobile-category-select"
                    class="w-full pl-2 pr-6 py-2 text-xs text-header font-semibold bg-transparent outline-none truncate appearance-none cursor-pointer">
                    <option value="">All</option>
                    @foreach ($headerCategories as $cat)
                        <option value="{{ $cat->slug }}"
                            {{ request('category') == $cat->slug ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-1 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-[10px] text-header"></i>
                </div>
            </div>

            <input type="text" name="search" id="mobile-search-input" autocomplete="off"
                value="{{ request('search') }}" placeholder="Find some product ..."
                class="flex-1 px-3 py-2 text-sm outline-none">

            <button type="submit" class="primary-bg text-primary px-4 py-2 font-bold text-sm">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <!-- Mobile Search Suggestions -->
        <div id="mobile-search-suggestions"
            class="hidden absolute top-full left-4 right-4 bg-white mt-1 rounded-b-lg shadow-2xl border border-gray-100 z-[3500] overflow-hidden pt-2">
            <div id="mobile-suggestion-content">
                <!-- পপুলার ও ট্রেন্ডিং ডাটা এখানে ডেক্সটপের মতোই থাকবে -->
                <div class="pb-2">
                    <p class="text-[9px] font-bold text-gray-400 uppercase px-4 py-2">জনপ্রিয় সার্চ</p>
                    @foreach ($popularSearches ?? [] as $item)
                        <a href="{{ route('shop.index', ['search' => $item->keyword]) }}"
                            class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-50">{{ $item->keyword }}</a>
                    @endforeach
                </div>
            </div>
            <div id="mobile-live-results" class="hidden py-2 border-t border-gray-50"></div>
        </div>
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
                <img src="{{ $setup->logo_url ?? asset('images/logo.jpeg') }}" alt="{{ $setup->shop_name }}" class="h-8 w-auto">
            @else
                <span class="text-xl font-bold text-header">খাঁটি ভাই</span>
            @endif
            <button onclick="toggleMobileMenu()" class="text-header text-2xl focus:outline-none">
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
                class="text-base font-semibold text-header hover:opacity-80 transition-opacity">হোমপেজ</a>
            <a href="{{ route('flash.sale') }}"
                class="text-base font-semibold text-header hover:opacity-80 transition-opacity">অফার</a>

            @foreach ($headerCategories as $cat)
                <a href="{{ route('category.products', $cat->slug) }}"
                    class="text-base font-semibold text-header hover:opacity-80 transition-opacity">
                    {{ $cat->name }}
                </a>
            @endforeach
        </nav>

        <!-- Bottom Actions -->
        <div class="mt-auto p-5 flex flex-col gap-3 mb-6">

            @auth('customer')
                <a href="{{ route('user.dashboard') }}"
                    class="flex items-center justify-center gap-3 primary-bg text-primary py-3 rounded shadow-sm font-bold text-base hover:bg-opacity-95 transition-all">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>আমার ড্যাশবোর্ড</span>
                </a>

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
            <a href="tel:{{ $setup->phone ?? '' }}"
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
    <div class="flex-1 overflow-y-auto p-4" id="mini-cart-list">
        <!-- কম্পোনেন্ট কল করা হলো -->
        <x-template1.cart-drawer-items />
    </div>

    <!-- Footer -->
    @if (\Gloudemans\Shoppingcart\Facades\Cart::count() > 0)
        <div class="p-4 border-t bg-gray-50" id="mini-cart-footer">
            <div class="flex justify-between items-center mb-4">
                <span class="text-lg font-bold text-gray-700">SUBTOTAL:</span>
                <span class="text-lg font-bold text-gray-900" id="mini-cart-subtotal-val">
                    {{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal() }}৳
                </span>
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
    document.addEventListener('DOMContentLoaded', function() {
        function setupSearch(inputId, categorySelectId, suggestionBoxId, contentId, resultsId, containerId) {
            const input = document.getElementById(inputId);
            const categorySelect = document.getElementById(categorySelectId); // ক্যাটাগরি সিলেক্ট আইডি
            const suggestionBox = document.getElementById(suggestionBoxId);
            const defaultContent = document.getElementById(contentId);
            const liveResults = document.getElementById(resultsId);
            const container = document.getElementById(containerId);

            if (!input) return;

            let debounceTimer;
            let abortController = null;

            input.addEventListener('focus', () => {
                suggestionBox.classList.remove('hidden');
            });

            input.addEventListener('input', function() {
                const query = this.value.trim();
                const selectedCategory = categorySelect.value; // বর্তমান সিলেক্ট করা ক্যাটাগরি নিন

                clearTimeout(debounceTimer);
                if (abortController) abortController.abort();

                if (query.length > 1) {
                    debounceTimer = setTimeout(() => {
                        abortController = new AbortController();
                        defaultContent.classList.add('hidden');
                        liveResults.classList.remove('hidden');
                        liveResults.innerHTML =
                            '<div class="px-5 py-3 text-xs text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>খোঁজা হচ্ছে...</div>';

                        // ক্যাটাগরি স্লাগটি প্যারামিটার হিসেবে পাঠানো হচ্ছে
                        fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}&category=${selectedCategory}`, {
                                signal: abortController.signal
                            })
                            .then(res => res.json())
                            .then(data => {
                                liveResults.innerHTML = '';
                                if (data.length > 0) {
                                    data.forEach(item => {
                                        const link = document.createElement('a');
                                        link.href = "{{ url('product') }}/" + item
                                            .slug;
                                        link.className =
                                            "flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors";
                                        link.innerHTML = `
                                            <img src="${item.thumbnail_url}" class="w-6 h-6 rounded object-cover border" onerror="this.src='{{ asset('images/no-image.png') }}'">
                                            <span class="truncate">${item.title}</span>
                                        `;
                                        liveResults.appendChild(link);
                                    });
                                } else {
                                    liveResults.innerHTML =
                                        '<div class="px-5 py-3 text-xs text-gray-400">পণ্য পাওয়া যায়নি।</div>';
                                }
                            })
                            .catch(err => {
                                if (err.name !== 'AbortError') console.error(err);
                            });
                    }, 500);
                } else {
                    defaultContent.classList.remove('hidden');
                    liveResults.classList.add('hidden');
                }
            });

            // বাইরে ক্লিক করলে বন্ধ করা
            document.addEventListener('click', (e) => {
                if (container && !container.contains(e.target)) {
                    suggestionBox.classList.add('hidden');
                }
            });
        }

        // ডেক্সটপ সার্চ অ্যাক্টিভেট
        setupSearch('header-search-input', 'header-category-select', 'search-suggestions', 'suggestion-content',
            'live-search-results', 'header-search-container');

        // মোবাইল সার্চ অ্যাক্টিভেট
        setupSearch('mobile-search-input', 'mobile-category-select', 'mobile-search-suggestions',
            'mobile-suggestion-content', 'mobile-live-results', 'mobile-search-container');
    });
</script>
<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
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
