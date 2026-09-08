@php
    $customMenu = \App\Models\MenuSetting::where('company_id', $setup->company_id ?? null)
        ->where('status', \App\Enums\Status::Active->value)
        ->where('type', 'menu')
        ->first();

    $menuItems = $customMenu ? $customMenu->items : null;
@endphp
<header class="w-full header-custom-bg sticky top-0 z-50">

    <!-- 1. Top Bar (Orange Row) -->
    <div class="primary-bg text-primary py-2 text-sm hidden sm:block">
        <div class="container mx-auto px-4 flex justify-between items-center">

            <div class="flex items-center gap-6">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                        </path>
                    </svg>
                    {{ $setup->phone }}
                </span>
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                    {{ $setup->email }}
                </span>
            </div>
            <div class="flex items-center gap-6">

                <a href="{{ route('faq.index') }}" class="hover:underline">Help</a>
            </div>
        </div>
    </div>

    <!-- 2. Main Header -->
    <div class="container mx-auto px-4 py-3 flex items-center justify-between gap-3 lg:gap-8">

        <!-- Logo Section -->
        <div class="flex items-center gap-3 flex-shrink-0">
            <button class="lg:hidden text-2xl text-gray-700 focus:outline-none" onclick="toggleMobileMenu()"
                aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo Section -->
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                @if ($setup && $setup->logo)
                    <img src="{{ $setup->logo_url ?? asset('images/logo.jpeg') }}" alt="{{ $setup->shop_name }}"
                        class="h-8 md:h-12 w-auto object-contain">
                @else
                    <span class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">
                        {{ $setup->shop_name ?? 'Bhaiya Digital' }}
                    </span>
                @endif
            </a>
        </div>

        <!-- Search Bar (Desktop + Tablet) -->
        <form action="{{ route('shop.index') }}" method="GET" class="hidden sm:flex flex-1 max-w-2xl relative"
            id="header-search-container">
            <div
                class="flex w-full items-center bg-white border border-gray-200 rounded-md p-1 shadow-xs z-30 relative">
                <label for="search-input" class="sr-only">Search Products</label>
                <input type="text" name="search" id="header-search-input" autocomplete="off"
                    value="{{ request('search') }}" placeholder="Find products..."
                    class="flex-1 bg-transparent px-3 py-2 text-sm text-gray-600 outline-none">
                <button type="submit"
                    class="primary-bg text-primary h-9 w-10 flex items-center justify-center rounded-md flex-shrink-0"
                    aria-label="Submit Search">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </div>

            <!-- Search Suggestions Dropdown -->
            <div id="search-suggestions"
                class="hidden absolute top-full left-0 w-full bg-white mt-1 rounded-b-xl shadow-2xl border border-gray-100 z-20 overflow-hidden pt-2">
                <div id="suggestion-content">
                    <div class="pb-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Popular
                            Searches</p>
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
                        <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Trending
                            Products</p>
                        @foreach ($relatedProducts as $p)
                            <a href="{{ route('shop.index', ['search' => $p->title]) }}"
                                class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <img src="{{ $p->thumbnail_url }}"
                                    class="w-6 h-6 rounded object-cover border border-gray-100">
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


            <!-- Mobile Search Toggle -->
            <button class="sm:hidden text-xl hover-text" onclick="toggleMobileSearch()">
                <i class="fas fa-search"></i>
            </button>

            <!-- Wishlist -->
            {{-- <a href="{{ route('user.dashboard') }}"
                class="hidden lg:flex flex items-center gap-1.5 hover-text transition-colors relative">
                <div class="relative">
                    <i class="fa-regular fa-heart text-xl"></i>
                    @auth('customer')
                    @php $initialWishCount = \App\Models\Wishlist::where('customer_id',
                    auth('customer')->id())->count(); @endphp
                    @else
                    @php $initialWishCount = 0; @endphp
                    @endauth
                    <span id="wishlist-count-nav"
                        class="absolute -top-2 -right-2 primary-bg text-primary text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white {{ $initialWishCount > 0 ? '' : 'hidden' }}">
                        {{ $initialWishCount }}
                    </span>
                </div>
                <span class="hidden lg:block  text-[16px]">Wishlist</span>
            </a> --}}

            <!-- Account -->
            <div class="relative cursor-pointer hidden lg:block" id="account-menu">
                <div onclick="toggleAccount()"
                    class="flex items-center gap-1.5 hover-text transition-colors select-none">
                    @auth('customer')
                        <div class="w-8 h-8 rounded-full overflow-hidden border border-gray-200 flex-shrink-0">
                            <img src="{{ auth('customer')->user()->profile_url ?? asset('./images/template1/frontend/user.avif') }}"
                                alt="User" class="w-full h-full object-cover">
                        </div>
                    @else
                        <i class="fa-regular fa-user text-xl"></i>
                    @endauth
                    <span class="hidden lg:block  text-[16px]">Account</span>
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
                                class="flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-gray-700 hover-text hover:bg-orange-50 transition-colors">
                                <i class="fas fa-box text-gray-400 w-4"></i>
                                Dashboard
                            </a>
                        </div>
                        <div class="border-t border-gray-100 py-1">
                            <form action="{{ route('user.logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-red-500 hover:bg-red-50 transition-colors"
                                    aria-label="Logout">
                                    <i class="fa-solid fa-right-from-bracket w-4"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="py-2">
                            <a href="{{ route('user.login') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover-text hover:bg-orange-50 transition-colors">
                                <i class="fa-solid fa-right-to-bracket text-gray-400 text-sm"></i>
                                Login
                            </a>
                            <a href="{{ route('user.register') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover-text hover:bg-orange-50 transition-colors">
                                <i class="fa-solid fa-user-plus text-gray-400 text-sm"></i>
                                Register
                            </a>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Cart -->
            <a href="{{ route('cart.index') }}"
                class="lg:flex flex hidden items-center gap-1.5 hover-text transition-colors relative">
                <div class="relative">
                    <i class="fa-solid fa-cart-shopping text-xl"></i>
                    <span
                        class="cart-count-nav absolute -top-2 -right-2 primary-bg text-primary text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white">
                        {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}
                    </span>
                </div>
                <span class="hidden lg:block  text-[16px]">Cart</span>
            </a>

        </div>
    </div>

    <!-- Mobile Search Bar (Full Professional Design) -->
    <div id="mobile-search-bar" class="hidden sm:hidden px-4 pb-3 relative" id="mobile-search-container">
        <form action="{{ route('shop.index') }}" method="GET" class="relative">
            <div class="flex items-center bg-white border border-gray-200 rounded-md p-1 relative z-30 shadow-sm">
                <label for="mobile-search-input" class="sr-only">Search Products</label>
                <input type="text" name="search" id="mobile-search-input" autocomplete="off"
                    value="{{ request('search') }}" placeholder="Find products..."
                    class="flex-1 bg-transparent px-3 py-2 text-sm text-gray-600 outline-none">
                <button type="submit"
                    class="primary-bg text-primary h-9 w-10 flex items-center justify-center rounded-md">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </div>

            <!-- Mobile Search Suggestions Dropdown (Matches Desktop Style) -->
            <div id="mobile-search-suggestions"
                class="hidden absolute top-full left-0 w-full bg-white mt-1 rounded-b-xl shadow-2xl border border-gray-100 z-[100] overflow-hidden pt-2">

                <div id="mobile-suggestion-content">
                    <!-- Popular Searches -->
                    <div class="pb-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Popular
                            Searches</p>
                        @forelse($popularSearches as $item)
                            <a href="{{ route('shop.index', ['search' => $item->keyword]) }}"
                                class="flex items-center justify-between px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
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

                    <!-- Trending Products -->
                    <div class="border-t border-gray-50 pt-2 pb-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Trending
                            Products</p>
                        @foreach ($relatedProducts as $p)
                            <a href="{{ route('shop.index', ['search' => $p->title]) }}"
                                class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <img src="{{ $p->thumbnail_url }}"
                                    class="w-6 h-6 rounded object-cover border border-gray-100"
                                    onerror="this.src='{{ asset('images/no-image.png') }}'">
                                <span class="truncate">{{ $p->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Live Search Results (Shows when typing) -->
                <div id="mobile-live-search-results" class="hidden py-2 border-t border-gray-50"></div>
            </div>
        </form>
    </div>

    <!-- 3. Bottom Category Nav (Desktop) -->
    <style>
        #desktop-bottom-nav .nav-dropdown-item:hover>.nav-dropdown-menu {
            display: block !important;
        }

        #desktop-bottom-nav .nav-sub-item:hover>.nav-sub-dropdown-menu {
            display: block !important;
        }
    </style>
    <div class="border-t border-gray-100 hidden md:block" id="desktop-bottom-nav">
        <div class="container mx-auto px-2 flex items-center gap-6 py-2.5 overflow-x-auto no-scrollbar">

            @if ($menuItems && count($menuItems) > 0)
                @foreach (collect($menuItems)->sortBy('order') as $item)
                    @if (data_get($item, 'visible') === true)
                        @php
                            $link = $item['link'] ?? '#';
                            $finalUrl = str_starts_with($link, 'http') ? $link : url($link);
                        @endphp
                        <div class="nav-dropdown-item group relative flex-shrink-0">
                            <a href="{{ $finalUrl }}"
                                class="flex items-center gap-1.5 text-[16px] hover-text whitespace-nowrap {{ !$loop->last ? 'border-r border-gray-300 pr-[20px]' : '' }}">
                                <span>{{ $item['label'] }}</span>
                                @if (!empty($item['children']))
                                    <i
                                        class="fa-solid fa-chevron-down text-[10px] opacity-70 group-hover:rotate-180 transition-transform duration-200"></i>
                                @endif
                            </a>

                            @if (!empty($item['children']))
                                <div class="nav-dropdown-menu absolute left-0 top-full hidden group-hover:block z-[100] pt-1">
                                    <ul
                                        class="relative w-64 bg-white shadow-2xl border border-gray-100 py-2 rounded-b-md text-gray-700 text-sm font-medium">
                                        @foreach (collect($item['children'])->sortBy('order') as $child)
                                            @if (data_get($child, 'visible') === true)
                                                @php
                                                    $childLink = $child['link'] ?? '#';
                                                    $childUrl = str_starts_with($childLink, 'http') ? $childLink : url($childLink);
                                                @endphp
                                                <li
                                                    class="nav-sub-item group/sub px-4 py-2.5 hover:bg-gray-50 flex justify-between items-center cursor-pointer relative border-b border-gray-50 last:border-0">
                                                    <a href="{{ $childUrl }}"
                                                        class="group-hover/sub:text-[var(--primary-color,#016738)] text-gray-700 uppercase flex-1 text-xs md:text-sm font-semibold transition-colors">
                                                        {{ $child['label'] }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            @else
                <a href="{{ route('home') }}"
                    class=" text-[16px] hover-text whitespace-nowrap border-r border-gray-300  pr-[20px]">
                    Home
                </a>
                <a href="{{ route('brand.index') }}"
                    class="  text-[16px] hover-text whitespace-nowrap  border-r border-gray-300  pr-[20px]">
                    Brands
                </a>
                <a href="{{ route('shop.index') }}"
                    class=" text-[16px] hover-text whitespace-nowrap  border-r border-gray-300  pr-[20px]">
                    All Products
                </a>
                <a href="{{ route('flash.sale') }}"
                    class=" f text-[16px] hover-text whitespace-nowrap  border-r border-gray-300  pr-[20px]">
                    Flash Sale 🔥
                </a>
                <a href="{{ route('order.track') }}"
                    class="  text-[16px] hover-text whitespace-nowrap  border-r border-gray-300  pr-[20px]">
                    Track Order
                </a>
                <a href="{{ route('contact.index') }}" class=" text-[16px] hover-text whitespace-nowrap">
                    Contact
                </a>
            @endif
        </div>
    </div>

    <!-- Mobile Menu Drawer -->
    <div id="mobile-menu" class="hidden  lg:hidden fixed inset-0 z-[1100] flex">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50" onclick="toggleMobileMenu()"></div>

        <!-- Drawer -->
        <div class="relative w-72 max-w-[85vw] bg-white h-full overflow-y-auto shadow-2xl flex flex-col">

            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 primary-bg text-primary">
                <span class="text-primary font-bold text-lg">{{ $setup->shop_name ?? 'Menu' }}</span>
                <button onclick="toggleMobileMenu()" class="text-primary text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- User Info -->
            @auth('customer')
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-orange-50">
                    <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-[var(--primary-color)]">
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
                        class="flex-1 text-center py-2 border border-[var(--primary-color)] text-header rounded-lg text-sm font-bold">Login</a>
                    <a href="{{ route('user.register') }}"
                        class="flex-1 text-center py-2 primary-bg text-primary text-primary rounded-lg text-sm font-bold">Register</a>
                </div>
            @endauth

            <!-- Nav Links -->
            <nav class="flex-1 px-4 py-3">
                @if ($menuItems && count($menuItems) > 0)
                    <p class="text-[10px] font-bold text-gray-400 uppercase px-2 py-2 tracking-wider">Menu</p>
                    @foreach (collect($menuItems)->sortBy('order') as $item)
                        @if (data_get($item, 'visible') === true)
                            @php
                                $link = $item['link'] ?? '#';
                                $finalUrl = str_starts_with($link, 'http') ? $link : url($link);
                                $hasChildren = !empty($item['children']);
                            @endphp
                            <div class="mb-1">
                                <div
                                    class="flex items-center justify-between px-3 py-3 rounded-lg hover:bg-orange-50 transition-colors">
                                    <a href="{{ $finalUrl }}"
                                        class="flex items-center gap-3 text-sm font-medium text-gray-700 hover-text flex-1">
                                        {{ $item['label'] }}
                                    </a>
                                    @if ($hasChildren)
                                        <button type="button" class="accordion-btn p-1 text-gray-400 focus:outline-none"
                                            data-target="mobile-m1-{{ $loop->index }}">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                        </button>
                                    @endif
                                </div>

                                @if ($hasChildren)
                                    <div id="mobile-m1-{{ $loop->index }}" class="hidden bg-gray-50 rounded-lg ml-2 mt-1">
                                        @foreach (collect($item['children'])->sortBy('order') as $child)
                                            @if (data_get($child, 'visible') === true)
                                                @php
                                                    $childLink = $child['link'] ?? '#';
                                                    $childUrl = str_starts_with($childLink, 'http') ? $childLink : url($childLink);
                                                @endphp
                                                <a href="{{ $childUrl }}"
                                                    class="block px-6 py-2.5 text-xs font-medium text-gray-600 hover-text border-b border-gray-100 last:border-0 transition-colors">
                                                    {{ $child['label'] }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                @else
                    <p class="text-[10px] font-bold text-gray-400 uppercase px-2 py-2 tracking-wider">Navigation</p>

                    <a href="{{ route('home') }}"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover-text transition-colors">
                        <i class="fas fa-home w-4 text-gray-400"></i> Home
                    </a>
                    <a href="{{ route('shop.index') }}"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover-text transition-colors">
                        <i class="fas fa-store w-4 text-gray-400"></i> Shop
                    </a>
                    <a href="{{ route('flash.sale') }}"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-bold text-red-700 transition-colors">
                        <i class="fas fa-bolt w-4 text-red-600"></i> Flash Sale 🔥
                    </a>
                    <a href="{{ route('brand.index') }}"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover-text transition-colors">
                        <i class="fas fa-tags w-4 text-gray-400"></i> Brands
                    </a>
                    <a href="{{ route('blog.index') }}"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover-text transition-colors">
                        <i class="fas fa-newspaper w-4 text-gray-400"></i> Blog
                    </a>
                    <a href="{{ route('order.track') }}"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover-text transition-colors">
                        <i class="fas fa-truck w-4 text-gray-400"></i> Track Order
                    </a>
                    <a href="{{ route('contact.index') }}"
                        class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover-text transition-colors">
                        <i class="fas fa-envelope w-4 text-gray-400"></i> Contact
                    </a>

                    @if (isset($headerCategories) && $headerCategories->count())
                        <p class="text-[10px] font-bold text-gray-400 uppercase px-2 py-2 mt-3 tracking-wider">Categories
                        </p>
                        @foreach ($headerCategories as $cat)
                            <a href="{{ url($cat->slug ?? $cat->id) }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-orange-50 text-sm font-medium text-gray-700 hover-text transition-colors">
                                <i class="fas fa-chevron-right text-[10px] text-gray-300 w-4"></i>
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    @endif
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
                            class="w-full flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-red-50 text-sm font-medium text-red-500 transition-colors"
                            aria-label="Logout">
                            <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
    <!-- Bottom Navigation Bar (First Image Style) -->
    <div
        class="sm:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-100 flex justify-around items-center py-2 z-[1000] shadow-[0_-5px_15px_rgba(0,0,0,0.05)]">

        <!-- Home -->
        <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 text-gray-700">
            <img src="{{ asset('./images/template1/frontend/home.png') }}" alt="home" height="24px" width="24px">
            <span class="text-xs font-medium">Home</span>
        </a>

        <!-- Category (Triggers the existing Mobile Menu) -->
        <button onclick="toggleMobileMenu()" class="flex flex-col items-center gap-1 text-gray-700">
            <img src="{{ asset('./images/template1/frontend/app.png') }}" height="24px" width="24px" alt="Category">
            <span class="text-xs font-medium">Category</span>
        </button>

        <!-- Cart -->
        <a href="{{ route('cart.index') }}" class="flex flex-col items-center gap-1 text-gray-700 relative">
            <img src="{{ asset('./images/template1/frontend/sell.png') }}" alt="Cart" height="24px" width="24px">
            <span
                class="absolute -top-1 -right-2 primary-bg text-primary text-[9px] font-bold px-1 rounded-full border border-white">
                {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}
            </span>
            <span class="text-xs font-medium">Cart</span>
        </a>

        <!-- Profile -->
        <a href="{{ route('user.dashboard') }}" class="flex flex-col items-center gap-1 text-gray-700">
            @auth('customer')
                <div class="w-8 h-8 rounded-full overflow-hidden border border-gray-200 flex-shrink-0">
                    <img src="{{ auth('customer')->user()->profile_url ?? asset('./images/template1/frontend/user.avif') }}"
                        alt="User" class="w-full h-full object-cover">
                </div>
                <div>
                    <p class="text-xs font-medium">{{ auth('customer')->user()->name }}</p>
                </div>
            @else
                <img src="{{ asset('./images/template1/frontend/people.png') }}" alt="Profile" height="24px" width="24px">
                <span class="text-xs font-medium">Profile</span>
            @endauth
        </a>

    </div>

    <!-- Padding for Body to avoid content overlap -->
    <style>
        @media (max-width: 640px) {
            body {
                padding-bottom: 65px;
            }
        }
    </style>
</header>

@push('scripts')
    <script>
        // মোবাইল মেনু এবং সার্চ টগল ফাংশন (আগের মতোই থাকবে)
        function toggleMobileSearch() {
            const bar = document.getElementById('mobile-search-bar');
            bar.classList.toggle('hidden');
            if (!bar.classList.contains('hidden')) {
                document.getElementById('mobile-search-input').focus();
            }
        }

        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }

        function toggleAccount() {
            document.getElementById('account-dropdown').classList.toggle('hidden');
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchConfigs = [{
                input: document.getElementById('header-search-input'),
                suggestions: document.getElementById('search-suggestions'),
                results: document.getElementById('live-search-results'),
                defaultContent: document.getElementById('suggestion-content'),
                container: document.getElementById('header-search-container')
            },
            {
                input: document.getElementById('mobile-search-input'),
                suggestions: document.getElementById('mobile-search-suggestions'),
                results: document.getElementById('mobile-live-search-results'),
                defaultContent: document.getElementById('mobile-suggestion-content'),
                container: document.getElementById('mobile-search-container')
            }
            ];

            let debounceTimer;
            let abortController = null;

            searchConfigs.forEach(config => {
                if (!config.input) return;

                config.input.addEventListener('focus', () => {
                    config.suggestions.classList.remove('hidden');
                });

                config.input.addEventListener('input', function () {
                    const query = this.value.trim();
                    clearTimeout(debounceTimer);
                    if (abortController) abortController.abort();

                    if (query.length > 1) {
                        debounceTimer = setTimeout(() => {
                            abortController = new AbortController();

                            if (config.defaultContent) config.defaultContent.classList.add(
                                'hidden');
                            config.results.classList.remove('hidden');
                            config.suggestions.classList.remove('hidden');
                            config.results.innerHTML =
                                '<div class="px-5 py-3 text-xs text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Searching...</div>';

                            fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`, {
                                signal: abortController.signal
                            })
                                .then(res => res.json())
                                .then(data => {
                                    config.results.innerHTML = '';
                                    if (data.length > 0) {
                                        data.forEach(item => {
                                            const link = document.createElement(
                                                'a');
                                            link.href =
                                                "{{ url('product') }}/" + item
                                                    .slug;
                                            link.className =
                                                "flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors";
                                            link.innerHTML = `
                                                <img src="${item.thumbnail_url}" class="w-7 h-7 rounded object-cover border border-gray-100" onerror="this.src='{{ asset('images/no-image.png') }}'">
                                                <span class="truncate">${item.title}</span>
                                            `;
                                            config.results.appendChild(link);
                                        });
                                    } else {
                                        config.results.innerHTML =
                                            '<div class="px-5 py-3 text-xs text-gray-400">No products found.</div>';
                                    }
                                });
                        }, 400);
                    } else {
                        if (config.defaultContent) config.defaultContent.classList.remove('hidden');
                        config.results.classList.add('hidden');
                    }
                });
            });

            document.addEventListener('click', (e) => {
                searchConfigs.forEach(config => {
                    if (config.input && !config.input.contains(e.target) && !config.suggestions
                        .contains(e.target)) {
                        config.suggestions.classList.add('hidden');
                    }
                });
            });
        });
        // Mobile Sidebar Accordion logic (for subcategories & mini-categories)
        document.querySelectorAll(".accordion-btn").forEach((btn) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                const targetId = this.getAttribute('data-target');
                const target = document.getElementById(targetId);
                const icon = this.querySelector("i");

                if (target) {
                    target.classList.toggle("hidden");
                    if (icon) {
                        icon.classList.toggle("fa-plus");
                        icon.classList.toggle("fa-minus");
                    }
                }
            });
        });
    </script>
@endpush