<header class="w-full bg-white sticky top-0 z-50 font-['Outfit']">

    <!-- 1. Top Bar (Orange Row) -->
    <div class="bg-[#FF6A00] text-white py-2 text-sm hidden lg:block">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    {{ $setup->phone }}
                </span>
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                    {{ $setup->email }}
                </span>
            </div>
            <div class="flex items-center gap-6">
                <span>Free Shipping on Orders Over $50</span>
                <a href="{{ route('support.index') }}" class="hover:underline">Help</a>
            </div>
        </div>
    </div>

    <!-- 2. Main Header -->
    <div class="container mx-auto px-4 py-3 flex items-center justify-between gap-3 lg:gap-8">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0">
            @if ($setup && $setup->logo)
                <img src="{{ $setup->logo_url }}" alt="{{ $setup->shop_name }}" class="h-10 md:h-12 w-auto object-contain">
            @else
                <div class="bg-[#FF6A00] w-9 h-10 md:w-10 md:h-12 flex items-center justify-center rounded-lg shadow-sm">
                    <span class="text-white text-xl md:text-2xl font-bold">
                        {{ substr($setup->shop_name ?? 'O', 0, 1) }}
                    </span>
                </div>
            @endif
        </a>

        <!-- Search Bar (Desktop + Tablet) -->
        <form action="{{ route('shop.index') }}" method="GET"
            class="hidden sm:flex flex-1 max-w-2xl relative" id="header-search-container">
            <div class="flex w-full items-center bg-white border border-gray-200 rounded-md p-1 shadow-xs z-30 relative">
                <input type="text" name="search" id="header-search-input" autocomplete="off"
                    value="{{ request('search') }}" placeholder="Find products..."
                    class="flex-1 bg-transparent px-3 py-2 text-sm text-gray-600 outline-none">
                <button type="submit"
                    class="bg-[#FF6A00] text-white h-9 w-10 flex items-center justify-center rounded-md flex-shrink-0">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </div>

            <!-- Search Suggestions Dropdown -->
            <div id="search-suggestions"
                class="hidden absolute top-full left-0 w-full bg-white mt-1 rounded-b-xl shadow-2xl border border-gray-100 z-20 overflow-hidden pt-2">
                <div id="suggestion-content">
                    <div class="pb-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Popular Searches</p>
                        @forelse($popularSearches as $item)
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
                        <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Trending Products</p>
                        @foreach ($relatedProducts as $p)
                            <a href="{{ route('shop.index', ['search' => $p->title]) }}"
                                class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <img src="{{ $p->thumbnail_url }}" class="w-6 h-6 rounded object-cover border border-gray-100">
                                <span class="truncate">{{ $p->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div id="live-search-results" class="hidden py-2 border-t border-gray-50"></div>
            </div>
        </form>

        <!-- Right Side Icons -->
        <div class="flex items-center gap-3 lg:gap-6 text-[#1D2128]">

            <!-- Nav Links (XL only) -->
            <div class="hidden xl:flex items-center gap-5 text-sm font-semibold text-gray-500">
                <a href="{{ route('order.track') }}" class="hover:text-[#FF6A00] transition-colors">Track Order</a>
                <a href="{{ route('contact.index') }}" class="hover:text-[#FF6A00] transition-colors">Contact</a>
                <a href="{{ route('blog.index') }}" class="hover:text-[#FF6A00] transition-colors">Blog</a>
            </div>

            <!-- Mobile Search Toggle -->
            <button class="sm:hidden text-xl hover:text-[#FF6A00]" onclick="toggleMobileSearch()">
                <i class="fas fa-search"></i>
            </button>

            <!-- Wishlist -->
            <a href="{{ route('user.dashboard') }}"
                class="flex items-center gap-1.5 hover:text-[#FF6A00] transition-colors relative">
                <div class="relative">
                    <i class="fa-regular fa-heart text-xl"></i>
                    @auth('customer')
                        @php $initialWishCount = \App\Models\Wishlist::where('customer_id', auth('customer')->id())->count(); @endphp
                    @else
                        @php $initialWishCount = 0; @endphp
                    @endauth
                    <span id="wishlist-count-nav"
                        class="absolute -top-2 -right-2 bg-[#FF6A00] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white {{ $initialWishCount > 0 ? '' : 'hidden' }}">
                        {{ $initialWishCount }}
                    </span>
                </div>
                <span class="hidden lg:block font-semibold text-sm">Wishlist</span>
            </a>

            <!-- Account -->
            <div class="relative cursor-pointer hidden lg:block" id="account-menu">
                <div onclick="toggleAccount()"
                    class="flex items-center gap-1.5 hover:text-[#FF6A00] transition-colors select-none">
                    @auth('customer')
                        <div class="w-8 h-8 rounded-full overflow-hidden border border-gray-200 flex-shrink-0">
                            <img src="{{ auth('customer')->user()->profile_url ?? asset('./images/template1/frontend/user.avif') }}"
                                alt="User" class="w-full h-full object-cover">
                        </div>
                    @else
                        <i class="fa-regular fa-user text-xl"></i>
                    @endauth
                    <span class="hidden lg:block font-semibold text-sm">Account</span>
                    <i class="fas fa-chevron-down text-xs text-gray-400 hidden lg:block"></i>
                </div>

                <!-- Dropdown -->
                <div id="account-dropdown"
                    class="hidden absolute right-0 top-[calc(100%+10px)] w-56 bg-white rounded-lg shadow-lg border border-gray-100 z-50 overflow-hidden">
                    @auth('customer')
                        <div class="px-5 py-4 border-b border-gray-50">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ auth('customer')->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate mt-0.5">{{ auth('customer')->user()->email }}</p>
                        </div>
                        <div class="py-2">
                            <a href="{{ route('user.dashboard') }}?section=orders"
                                class="flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-[#FF6A00] hover:bg-orange-50 transition-colors">
                                <i class="fas fa-box text-gray-400 w-4"></i>
                                Dashboard
                            </a>
                        </div>
                        <div class="border-t border-gray-100 py-1">
                            <form action="{{ route('user.logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-red-500 hover:bg-red-50 transition-colors">
                                    <i class="fa-solid fa-right-from-bracket w-4"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="py-2">
                            <a href="{{ route('user.login') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-[#FF6A00] hover:bg-orange-50 transition-colors">
                                <i class="fa-solid fa-right-to-bracket text-gray-400 text-sm"></i>
                                Login
                            </a>
                            <a href="{{ route('user.register') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-[#FF6A00] hover:bg-orange-50 transition-colors">
                                <i class="fa-solid fa-user-plus text-gray-400 text-sm"></i>
                                Register
                            </a>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Cart -->
            <a href="{{ route('cart.index') }}"
                class="flex items-center gap-1.5 hover:text-[#FF6A00] transition-colors relative">
                <div class="relative">
                    <i class="fa-solid fa-cart-shopping text-xl"></i>
                    <span class="cart-count-nav absolute -top-2 -right-2 bg-[#FF6A00] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
                        {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}
                    </span>
                </div>
                <span class="hidden lg:block font-semibold text-sm">Cart</span>
            </a>

            <!-- Mobile Menu Toggle -->
            <button class="lg:hidden text-xl hover:text-[#FF6A00]" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Search Bar (hidden by default) -->
    <div id="mobile-search-bar" class="hidden sm:hidden px-4 pb-3">
        <form action="{{ route('shop.index') }}" method="GET">
            <div class="flex items-center bg-white border border-gray-200 rounded-md p-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Find products..."
                    class="flex-1 bg-transparent px-3 py-2 text-sm text-gray-600 outline-none">
                <button type="submit"
                    class="bg-[#FF6A00] text-white h-9 w-10 flex items-center justify-center rounded-md">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- 3. Bottom Category Nav (Desktop) -->
    <div class="border-t border-gray-100 hidden md:block">
        <div class="container mx-auto px-4 flex items-center gap-6 py-2.5 overflow-x-auto no-scrollbar">
            @foreach ($headerCategories as $cat)
                <a class="text-sm font-medium hover:text-[#FF6A00] whitespace-nowrap transition-colors"
                    href="{{ route('category.products', $cat->slug) }}">
                    {{ $cat->name }}
                </a>
            @endforeach
            <a class="text-sm font-medium hover:text-[#FF6A00] whitespace-nowrap" href="{{ route('brand.index') }}">Brands</a>
            <a class="text-sm font-medium text-red-500 hover:text-red-600 whitespace-nowrap" href="{{ route('flash.sale') }}">Flash Sale 🔥</a>
        </div>
    </div>

    <!-- Mobile Menu Drawer -->
    <div id="mobile-menu"
        class="hidden lg:hidden fixed inset-0 z-[999] flex">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50" onclick="toggleMobileMenu()"></div>

        <!-- Drawer -->
        <div class="relative w-72 max-w-[85vw] bg-white h-full overflow-y-auto shadow-2xl flex flex-col">

            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-[#FF6A00]">
                <span class="text-white font-bold text-lg">{{ $setup->shop_name ?? 'Menu' }}</span>
                <button onclick="toggleMobileMenu()" class="text-white text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- User Info -->
            @auth('customer')
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-orange-50">
                    <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-[#FF6A00]">
                        <img src="{{ auth('customer')->user()->profile_url ?? asset('./images/template1/frontend/user.avif') }}"
                            class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ auth('customer')->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth('customer')->user()->email }}</p>
                    </div>
                </div>
            @else
                <div class="flex gap-3 px-5 py-4 border-b border-gray-100">
                    <a href="{{ route('user.login') }}"
                        class="flex-1 text-center py-2 border border-[#FF6A00] text-[#FF6A00] rounded-lg text-sm font-bold">Login</a>
                    <a href="{{ route('user.register') }}"
                        class="flex-1 text-center py-2 bg-[#FF6A00] text-white rounded-lg text-sm font-bold">Register</a>
                </div>
            @endauth

            <!-- Nav Links -->
            <nav class="flex-1 px-4 py-3">
                <p class="text-[10px] font-bold text-gray-400 uppercase px-2 py-2 tracking-wider">Navigation</p>

                <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover:text-[#FF6A00] transition-colors">
                    <i class="fas fa-home w-4 text-gray-400"></i> Home
                </a>
                <a href="{{ route('shop.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover:text-[#FF6A00] transition-colors">
                    <i class="fas fa-store w-4 text-gray-400"></i> Shop
                </a>
                <a href="{{ route('flash.sale') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-red-500 transition-colors">
                    <i class="fas fa-bolt w-4 text-red-400"></i> Flash Sale 🔥
                </a>
                <a href="{{ route('brand.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover:text-[#FF6A00] transition-colors">
                    <i class="fas fa-tags w-4 text-gray-400"></i> Brands
                </a>
                <a href="{{ route('blog.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover:text-[#FF6A00] transition-colors">
                    <i class="fas fa-newspaper w-4 text-gray-400"></i> Blog
                </a>
                <a href="{{ route('order.track') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover:text-[#FF6A00] transition-colors">
                    <i class="fas fa-truck w-4 text-gray-400"></i> Track Order
                </a>
                <a href="{{ route('contact.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover:text-[#FF6A00] transition-colors">
                    <i class="fas fa-envelope w-4 text-gray-400"></i> Contact
                </a>

                @if(isset($headerCategories) && $headerCategories->count())
                    <p class="text-[10px] font-bold text-gray-400 uppercase px-2 py-2 mt-3 tracking-wider">Categories</p>
                    @foreach ($headerCategories as $cat)
                        <a href="{{ route('category.products', $cat->slug) }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover:text-[#FF6A00] transition-colors">
                            <i class="fas fa-chevron-right text-[10px] text-gray-300 w-4"></i>
                            {{ $cat->name }}
                        </a>
                    @endforeach
                @endif
            </nav>

            <!-- Bottom Actions -->
            @auth('customer')
                <div class="border-t border-gray-100 p-4">
                    <a href="{{ route('user.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-tachometer-alt w-4 text-gray-400"></i> Dashboard
                    </a>
                    <form action="{{ route('user.logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-red-50 text-sm font-medium text-red-500 transition-colors">
                            <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>

</header>

@push('scripts')
<script>
    // Mobile Search Toggle
    function toggleMobileSearch() {
        const bar = document.getElementById('mobile-search-bar');
        bar.classList.toggle('hidden');
        if (!bar.classList.contains('hidden')) {
            bar.querySelector('input').focus();
        }
    }

    // Mobile Menu Toggle
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }

    // Account Dropdown
    function toggleAccount() {
        document.getElementById('account-dropdown').classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('account-menu');
        const dropdown = document.getElementById('account-dropdown');
        if (menu && !menu.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Search Suggestions
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('header-search-input');
        const suggestionBox = document.getElementById('search-suggestions');
        const defaultContent = document.getElementById('suggestion-content');
        const liveResults = document.getElementById('live-search-results');
        const container = document.getElementById('header-search-container');

        if (!searchInput) return;

        let debounceTimer;
        let abortController = null;

        searchInput.addEventListener('focus', () => {
            suggestionBox.classList.remove('hidden');
        });

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(debounceTimer);
            if (abortController) abortController.abort();

            if (query.length > 1) {
                debounceTimer = setTimeout(() => {
                    abortController = new AbortController();
                    defaultContent.classList.add('hidden');
                    liveResults.classList.remove('hidden');
                    liveResults.innerHTML = '<div class="px-5 py-3 text-xs text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Searching...</div>';

                    fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`, { signal: abortController.signal })
                        .then(res => res.json())
                        .then(data => {
                            liveResults.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(item => {
                                    const link = document.createElement('a');
                                    link.href = "{{ route('shop.index') }}?search=" + encodeURIComponent(item.title);
                                    link.className = "flex items-center justify-between px-5 py-3 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-50 last:border-0";
                                    link.innerHTML = `<span>${item.title}</span><i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-300"></i>`;
                                    liveResults.appendChild(link);
                                });
                            } else {
                                liveResults.innerHTML = '<div class="px-5 py-3 text-xs text-gray-400">No products found.</div>';
                            }
                        })
                        .catch(error => {
                            if (error.name !== 'AbortError') console.error('Search error:', error);
                        });
                }, 500);
            } else {
                defaultContent.classList.remove('hidden');
                liveResults.classList.add('hidden');
            }
        });

        document.addEventListener('click', (e) => {
            if (container && !container.contains(e.target)) {
                suggestionBox.classList.add('hidden');
            }
        });
    });
</script>
@endpush
