<header class="w-full bg-white sticky top-0 z-50 shadow-sm">
    <!-- 1. Main Header (Logo, Search, User Actions) -->
    <div class="container mx-auto px-4 py-4 flex items-center justify-between gap-4 lg:gap-10">

        <!-- Mobile Menu Toggle -->
        <button onclick="toggleMobileMenu()" class="md:hidden text-gray-700 text-2xl focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex-shrink-0">
            @if ($setup && $setup->logo)
                <img src="{{ $setup->logo_url ?? asset('images/logo.png') }}" alt="{{ $setup->shop_name }}" width="200" height="48" class="h-8 md:h-12 w-auto">
            @else
                <span class="text-2xl font-black italic text-gray-900 tracking-tighter">KICK<span
                        class="text-blue-600">ZONE</span></span>
            @endif
        </a>

        <!-- Segmented Search Bar with Suggestions -->
        <form action="{{ route('shop.index') }}" method="GET" class="hidden md:flex flex-1 max-w-3xl relative"
            id="header-search-container">
            <div class="flex w-full border border-gray-300 rounded-md overflow-hidden bg-white z-30 relative">
                <!-- Category Dropdown -->
                <div class="relative flex-shrink-0 border-r border-gray-200 bg-gray-50">
                    <label for="category-dropdown" class="sr-only">Select Category</label>
                    <select name="category"
                        class="h-full pl-4 pr-10 py-2 text-base text-gray-700 bg-transparent outline-none appearance-none cursor-pointer">
                        <option value="">All Categories</option>
                        @foreach ($headerCategories as $cat)
                            <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>

                <!-- Input -->
                <input type="text" name="search" id="header-search-input" autocomplete="off"
                    value="{{ request('search') }}" placeholder="Search Products..."
                    class="flex-1 px-4 py-2 text-base text-gray-800 outline-none">

                <!-- Search Button -->
                <button type="submit" aria-label="search button"
                    class="primary-bg text-primary px-6 py-2 flex items-center justify-center hover:opacity-90 transition-all">
                    <i class="fas fa-search text-lg"></i>
                </button>
            </div>

            <!-- Search Suggestions Dropdown -->
            <div id="search-suggestions"
                class="hidden absolute top-full left-0 w-full bg-white mt-1 rounded-b-xl shadow-2xl border border-gray-100 z-20 overflow-hidden pt-2">
                <div id="suggestion-content">
                    <div class="pb-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Popular
                            Searches</p>
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
                            <p class="px-5 py-2 text-xs text-gray-400 italic">No search history</p>
                        @endforelse
                    </div>

                    <div class="border-t border-gray-50 pt-2 pb-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Trending
                            Products</p>
                        @foreach ($relatedProducts ?? [] as $p)
                            <a href="{{ route('product.details', $p->slug) }}"
                                class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <img src="{{ $p->thumbnail_url }}" height="" width=""
                                    class="w-6 h-6 rounded object-cover border border-gray-100">
                                <span class="truncate">{{ $p->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <!-- Live Search Results Container -->
                <div id="live-search-results" class="hidden py-2 border-t border-gray-50"></div>
            </div>
        </form>

        <!-- Right Side Icons -->
        <div class="flex items-center gap-5 lg:gap-8">
            <!-- Account -->
            <div class="relative group hidden md:block" id="desktop-account-wrapper">
                @auth('customer')
                    <button onclick="toggleDesktopAccount()"
                        class="flex items-center gap-1 text-gray-800 hover:text-blue-600 transition-colors cursor-pointer">
                        <i class="fa-regular fa-user text-2xl"></i>
                    </button>
                    <!-- Dropdown Logic remains same as your previous code -->
                    <div id="desktop-account-menu"
                        class="hidden absolute right-0 mt-3 w-52 bg-white border border-gray-100 rounded-lg shadow-xl z-50 py-2">
                        <a href="{{ route('user.dashboard') }}"
                            class="block px-4 py-2 text-sm hover:bg-gray-50">Dashboard</a>
                        <form action="{{ route('user.logout') }}" method="POST">@csrf <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Logout</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('user.login') }}" aria-label="Login to your account" class="text-gray-800 hover:text-blue-600 transition-colors">
                        <i class="fa-regular fa-user text-2xl"></i>
                    </a>
                @endauth
            </div>

            <!-- Cart Section -->
            <button onclick="toggleCartDrawer()" class="flex items-center gap-3 group outline-none">
                <div class="relative">
                    <i
                        class="fa-solid fa-basket-shopping text-2xl text-gray-800 group-hover:text-blue-600 transition-colors"></i>
                    <span
                        class="cart-count-nav absolute -top-2 -right-2 primary-bg text-white text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full border-2 border-white">
                        {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}
                    </span>
                </div>
                <div class="hidden sm:flex flex-col items-start leading-none">
                    <span
                        class="text-lg font-bold text-gray-900">{{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal() }}
                        {{ $setup->currency ?? '৳' }}</span>
                </div>
            </button>
        </div>
    </div>

    <!-- 2. Bottom Navigation (Category Links & Track Order) -->
    <div class="bg-[#F8FAFC] border-t border-gray-100 hidden md:block">
        <div class="container mx-auto px-4 flex items-center justify-between">
            <nav class="flex items-center gap-6 md:gap-8 py-3">
                <a href="{{ route('home') }}"
                    class="text-base font-normal text-[var(--primary-color)] hover:text-[var(--primary-color)] whitespace-nowrap">Home</a>
                <a href="{{ route('shop.index') }}"
                    class="text-base font-normal text-black hover:text-[var(--primary-color)] whitespace-nowrap transition-colors">Shop</a>
                @foreach ($headerCategories->take(7) as $cat)
                    <a href="{{ route('category.products', $cat->slug) }}"
                        class="text-base font-normal text-black hover:text-[var(--primary-color)] whitespace-nowrap transition-colors">{{ $cat->name }}</a>
                @endforeach
                <a href="{{ route('flash.sale') }}"
                    class="text-base font-normal text-black hover:text-[var(--primary-color)] whitespace-nowrap transition-colors">Offers</a>
            </nav>

            <!-- Track Order Button -->
            <a href="{{ route('order.track') }}"
                class="primary-bg text-primary px-6 py-2.5 my-2 rounded-md font-bold text-sm tracking-wide hover:opacity-90 transition-all uppercase">
                Track Order
            </a>
        </div>
    </div>

    <!-- Mobile Search Bar (Updated with Suggestions) -->
    <div class="md:hidden px-4 pb-4 relative" id="mobile-search-container">
        <form action="{{ route('shop.index') }}" method="GET"
            class="flex border border-gray-300 rounded-md overflow-hidden bg-white relative z-30">
            <input type="text" name="search" id="mobile-search-input" autocomplete="off"
                value="{{ request('search') }}" placeholder="Search Products.."
                class="flex-1 px-3 py-2 text-sm outline-none">
            <button type="submit" class="primary-bg text-primary px-4 py-2 font-bold text-sm">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <!-- Mobile Search Suggestions Dropdown -->
        <div id="mobile-search-suggestions"
            class="hidden absolute top-full left-4 right-4 bg-white mt-1 rounded-b-xl shadow-2xl border border-gray-100 z-20 overflow-hidden pt-2">
            <div id="mobile-suggestion-content">
                <div class="pb-2">
                    <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Popular Searches
                    </p>
                    @forelse($popularSearches ?? [] as $item)
                        <a href="{{ route('shop.index', ['search' => $item->keyword]) }}"
                            class="flex items-center justify-between px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-history text-gray-300 text-xs"></i>
                                <span>{{ $item->keyword }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="px-5 py-2 text-xs text-gray-400 italic">No search history</p>
                    @endforelse
                </div>

                <div class="border-t border-gray-50 pt-2 pb-2">
                    <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Trending Products
                    </p>
                    @foreach ($relatedProducts ?? [] as $p)
                        <a href="{{ route('product.details', $p->slug) }}"
                            class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <img src="{{ $p->thumbnail_url }}" height="" width=""
                                class="w-6 h-6 rounded object-cover border border-gray-100">
                            <span class="truncate">{{ $p->title }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div id="mobile-live-search-results" class="hidden py-2 border-t border-gray-50"></div>
        </div>
    </div>
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
                <img src="{{ $setup->logo_url }}" height="" width="" alt="{{ $setup->shop_name }}" class="h-8 w-auto">
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
                <a href="{{ route('user.login') }}" aria-label="Login to your account"
                    class="flex items-center justify-center gap-3 primary-bg text-primary py-3 rounded shadow-sm font-bold text-base hover:bg-opacity-95 transition-all">
                    <i class="fas fa-user-circle text-xl"></i>
                    <span>লগইন / রেজিস্টার</span>
                </a>
            @endauth

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
        <button onclick="toggleCartDrawer()" aria-label="Close Cart" class="text-gray-500 hover:text-red-500 text-2xl">
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
                    height="" width="" class="w-full h-full object-contain">
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-gray-800 leading-tight">{{ $item->name }}</h3>
                    <p class="text-xs text-gray-500 mt-1">পরিমাণ: {{ $item->options->variant ?? 'N/A' }}</p>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-sm font-bold text-[#016738]">{{ $item->qty }} ×
                            {{ number_format($item->price, 0) }}৳</span>
                        <a href="{{ route('cart.remove', $item->rowId) }}" aria-label="Remove item from cart" class="text-gray-400 hover:text-red-500">
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
    document.addEventListener('DOMContentLoaded', function() {
        /**
         * সার্চ সাজেশন ফাংশন
         * @param {string} inputId - ইনপুট ফিল্ডের আইডি
         * @param {string} suggestionBoxId - সাজেশন বক্সের আইডি
         * @param {string} contentId - ডিফল্ট কন্টেন্টের আইডি
         * @param {string} resultsId - লাইভ রেজাল্ট কন্টেইনার আইডি
         * @param {string} containerId - পুরো সার্চ এরিয়ার আইডি (বাইরে ক্লিক ডিটেক্ট করার জন্য)
         */
        function initUnifiedSearch(inputId, suggestionBoxId, contentId, resultsId, containerId) {
            const input = document.getElementById(inputId);
            const suggestionBox = document.getElementById(suggestionBoxId);
            const defaultContent = document.getElementById(contentId);
            const liveResults = document.getElementById(resultsId);
            const container = document.getElementById(containerId);

            if (!input) return;

            let debounceTimer;
            let abortController = null;

            // ইনপুট ফোকাস করলে বক্স দেখাবে
            input.addEventListener('focus', () => {
                suggestionBox.classList.remove('hidden');
            });

            // টাইপ করলে সার্চ শুরু হবে
            input.addEventListener('input', function() {
                const query = this.value.trim();
                clearTimeout(debounceTimer);
                if (abortController) abortController.abort();

                if (query.length > 1) {
                    debounceTimer = setTimeout(() => {
                        abortController = new AbortController();
                        defaultContent.classList.add('hidden');
                        liveResults.classList.remove('hidden');
                        liveResults.innerHTML =
                            '<div class="px-5 py-3 text-xs text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Searching...</div>';

                        fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`, {
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
                                        <img src="${item.thumbnail_url}" height="" width="" class="w-6 h-6 rounded object-cover border border-gray-100" onerror="this.src='{{ asset('images/no-image.png') }}'">
                                        <span class="truncate">${item.title}</span>
                                    `;
                                        liveResults.appendChild(link);
                                    });
                                } else {
                                    liveResults.innerHTML =
                                        '<div class="px-5 py-3 text-xs text-gray-400">No products found.</div>';
                                }
                            })
                            .catch(err => {
                                if (err.name !== 'AbortError') console.error(
                                    'Search error:', err);
                            });
                    }, 500);
                } else {
                    defaultContent.classList.remove('hidden');
                    liveResults.classList.add('hidden');
                }
            });

            // বাইরে ক্লিক করলে বন্ধ হবে
            document.addEventListener('click', (e) => {
                if (container && !container.contains(e.target)) {
                    suggestionBox.classList.add('hidden');
                }
            });
        }

        // ডেক্সটপ সার্চ চালু করুন
        initUnifiedSearch('header-search-input', 'search-suggestions', 'suggestion-content',
            'live-search-results', 'header-search-container');

        // মোবাইল সার্চ চালু করুন
        initUnifiedSearch('mobile-search-input', 'mobile-search-suggestions', 'mobile-suggestion-content',
            'mobile-live-search-results', 'mobile-search-container');
    });
</script>
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
