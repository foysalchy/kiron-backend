@php
    $customMenu = \App\Models\MenuSetting::where('company_id', $setup->company_id ?? null)
        ->where('status', \App\Enums\Status::Active->value)
        ->where('type', 'menu')
        ->first();

    $menuItems = $customMenu ? $customMenu->items : null;
@endphp
<header class="w-full header-custom-bg sticky top-0 z-50 shadow-sm">
    <!-- 1. Main Header (Logo, Search, User Actions) -->
    <div class="container mx-auto px-4 py-4 flex items-center justify-between gap-4 lg:gap-10">

        <!-- Mobile Menu Toggle -->
        <button onclick="toggleMobileMenu()" aria-label="Open menu" class="md:hidden text-gray-700 text-2xl focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex-shrink-0">
            @if ($setup && $setup->logo)
                <img src="{{ $setup->logo_url ?? asset('images/logo.jpeg') }}" height="80" width="200"
                    alt="{{ $setup->shop_name ?? 'Shop' }} Logo" class="h-12 md:h-16 w-auto object-contain" />
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
                    <select id="category-dropdown" name="category"
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
                            <a href="{{ url($p->slug) }}"
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
                    <button onclick="toggleDesktopAccount()" aria-label="Open account menu"
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
                    <a href="{{ route('user.login') }}" aria-label="Login to your account"
                        class="text-gray-800 hover:text-blue-600 transition-colors">
                        <i class="fa-regular fa-user text-2xl"></i>
                    </a>
                @endauth
            </div>

            <!-- Cart Section -->
            <button onclick="toggleCartDrawer()" class="relative group outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 md:h-8 md:w-8 text-header" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span
                    class="cart-count-nav absolute -top-1 -right-1 bg-black text-primary text-[10px] font-bold h-4 w-4 md:h-5 md:w-5 flex items-center justify-center rounded-full border-2 border-white">
                    {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}
                </span>
            </button>
        </div>
    </div>

    <!-- 2. Bottom Navigation (Category Links & Track Order) -->
    <style>
        /* Ensure category dropdowns show reliably on desktop hover */
        #desktop-bottom-nav .nav-dropdown-item:hover>.nav-dropdown-menu {
            display: block !important;
        }

        #desktop-bottom-nav .nav-sub-item:hover>.nav-sub-dropdown-menu {
            display: block !important;
        }
    </style>
    <div class="bg-[#F8FAFC] border-t border-gray-100 hidden md:block" id="desktop-bottom-nav">
        <div class="container mx-auto px-4 flex items-center justify-between">
            <ul class="flex items-center gap-6 md:gap-8 py-0 list-none m-0 p-0">
                @if ($menuItems && count($menuItems) > 0)
                    @foreach (collect($menuItems)->sortBy('order') as $item)
                        @if (data_get($item, 'visible') === true)
                            @php
                                $link = $item['link'] ?? '#';
                                $finalUrl = str_starts_with($link, 'http') ? $link : url($link);
                            @endphp
                            <li class="nav-dropdown-item group relative flex-shrink-0 py-3">
                                <a href="{{ $finalUrl }}"
                                    class="flex items-center gap-1.5 text-base font-normal text-black group-hover:text-header whitespace-nowrap transition-colors">
                                    <span>{{ $item['label'] }}</span>
                                    @if (!empty($item['children']))
                                        <i
                                            class="fa-solid fa-chevron-down text-[10px] opacity-70 group-hover:rotate-180 transition-transform duration-200"></i>
                                    @endif
                                </a>

                                @if (!empty($item['children']))
                                    <div class="nav-dropdown-menu absolute left-0 top-full hidden group-hover:block z-[100] pt-0">
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
                                                            class="group-hover/sub:text-[var(--primary-color,#016738)] text-gray-700 flex-1 text-sm font-semibold transition-colors">
                                                            {{ $child['label'] }}
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </li>
                        @endif
                    @endforeach
                @else
                    <li class="flex-shrink-0">
                        <a href="{{ route('home') }}"
                            class="text-base font-normal text-header hover:text-header whitespace-nowrap py-3 block">Home</a>
                    </li>
                    <li class="flex-shrink-0">
                        <a href="{{ route('shop.index') }}"
                            class="text-base font-normal text-black hover:text-header whitespace-nowrap transition-colors py-3 block">Shop</a>
                    </li>
                    @foreach ($headerCategories->take(7) as $mega)
                        @php
                            $hasSub = $mega->subCategories && $mega->subCategories->count() > 0;
                        @endphp
                        <li class="nav-dropdown-item group relative flex-shrink-0 py-3">
                            <a href="{{ url($mega->slug ?? $mega->id) }}"
                                class="flex items-center gap-1.5 text-base font-normal text-black group-hover:text-header whitespace-nowrap transition-colors">
                                <span>{{ $mega->name }}</span>
                                @if ($hasSub)
                                    <i
                                        class="fa-solid fa-chevron-down text-[10px] opacity-70 group-hover:rotate-180 transition-transform duration-200"></i>
                                @endif
                            </a>

                            @if ($hasSub)
                                <div class="nav-dropdown-menu absolute left-0 top-full hidden group-hover:block z-[100] pt-0">
                                    <ul
                                        class="relative w-64 bg-white shadow-2xl border border-gray-100 py-2 rounded-b-md text-gray-700 text-base font-normal">
                                        @foreach ($mega->subCategories as $sub)
                                            @php
                                                $hasMini = $sub->miniCategories && $sub->miniCategories->count() > 0;
                                            @endphp
                                            <li
                                                class="nav-sub-item group/sub px-4 py-2.5 hover:bg-gray-50 flex justify-between items-center cursor-pointer relative border-b border-gray-50 last:border-0">
                                                <a href="{{ url($sub->slug ?? $sub->id) }}"
                                                    class="group-hover/sub:text-[var(--primary-color,#016738)] text-gray-700 flex-1 text-base font-normal transition-colors">
                                                    {{ $sub->name }}
                                                </a>
                                                @if ($hasMini)
                                                    <i
                                                        class="fa-solid fa-chevron-right text-xs text-gray-400 group-hover/sub:text-[var(--primary-color,#016738)] group-hover/sub:translate-x-0.5 transition-transform"></i>
                                                @endif

                                                @if ($hasMini)
                                                    <ul
                                                        class="nav-sub-dropdown-menu absolute {{ (isset($loop->parent) && $loop->parent->remaining < 2) ? 'right-full border-r' : 'left-full border-l' }} top-0 w-60 min-h-full bg-white shadow-2xl border-gray-100 py-2 hidden group-hover/sub:block rounded-md">
                                                        @foreach ($sub->miniCategories as $mini)
                                                            <li class="px-4 py-2 hover:bg-gray-100 border-b border-gray-50 last:border-0">
                                                                <a href="{{ url($mini->slug ?? $mini->id) }}"
                                                                    class="block hover:text-[var(--primary-color,#016738)] text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors">
                                                                    {{ $mini->name }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </li>
                    @endforeach
                    <li class="flex-shrink-0">
                        <a href="{{ route('flash.sale') }}"
                            class="text-base font-normal text-black hover:text-header whitespace-nowrap transition-colors py-3 block">Offers</a>
                    </li>
                @endif
            </ul>

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
            <input type="text" name="search" id="mobile-search-input" autocomplete="off" value="{{ request('search') }}"
                placeholder="Search Products.." class="flex-1 px-3 py-2 text-sm outline-none">
            <button type="submit" aria-label="Search" class="primary-bg text-primary px-4 py-2 font-bold text-sm">
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
                        <a href="{{ url($p->slug) }}"
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
                <img src="{{ $setup->logo_url ?? asset('images/logo.jpeg') }}" height="" width=""
                    alt="{{ $setup->shop_name }}" class="h-8 w-auto">
            @else
                <span class="text-xl font-bold [var(--primary-color)]">খাঁটি ভাই</span>
            @endif
            <button onclick="toggleMobileMenu()" class="[var(--primary-color)] text-2xl focus:outline-none">
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
        <nav class="flex-1 overflow-y-auto p-4 space-y-1" style="-webkit-overflow-scrolling: touch;">
            @if ($menuItems && count($menuItems) > 0)
                @foreach (collect($menuItems)->sortBy('order') as $item)
                    @if (data_get($item, 'visible') === true)
                        @php
                            $link = $item['link'] ?? '#';
                            $finalUrl = str_starts_with($link, 'http') ? $link : url($link);
                            $hasChildren = !empty($item['children']);
                        @endphp
                        <div class="border-b border-gray-100 last:border-0">
                            <div class="flex items-center justify-between px-3 py-2.5 hover:bg-gray-50 rounded-lg">
                                <a href="{{ $finalUrl }}" class="text-sm font-semibold uppercase text-black flex-1">
                                    {{ $item['label'] }}
                                </a>
                                @if ($hasChildren)
                                    <button type="button" class="accordion-btn p-1.5 text-black focus:outline-none"
                                        data-target="mobile-custom-{{ $loop->index }}">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                @endif
                            </div>

                            @if ($hasChildren)
                                <div id="mobile-custom-{{ $loop->index }}"
                                    class="hidden bg-gray-50 rounded-lg mb-1 border-t border-gray-100">
                                    @foreach (collect($item['children'])->sortBy('order') as $child)
                                        @if (data_get($child, 'visible') === true)
                                            @php
                                                $childLink = $child['link'] ?? '#';
                                                $childUrl = str_starts_with($childLink, 'http') ? $childLink : url($childLink);
                                            @endphp
                                            <div class="border-b border-gray-200/60 last:border-0">
                                                <div class="flex items-center justify-between pl-6 pr-3 py-2">
                                                    <a href="{{ $childUrl }}"
                                                        class="text-xs font-medium text-black flex-1 hover:text-[var(--primary-color)]">
                                                        {{ $child['label'] }}
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            @else
                <a href="{{ route('home') }}"
                    class="block px-3 py-2 text-sm font-semibold uppercase text-black hover:bg-gray-50 rounded-lg transition-colors">হোমপেজ</a>
                <a href="{{ route('flash.sale') }}"
                    class="block px-3 py-2 text-sm font-semibold uppercase text-black hover:bg-gray-50 rounded-lg transition-colors">অফার</a>

                <div class="pt-2 mt-2 border-t border-gray-100">
                    <p class="px-3 py-1.5 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">ক্যাটাগরি সমূহ
                    </p>
                    @foreach ($headerCategories as $mega)
                        @php
                            $hasSub = $mega->subCategories && $mega->subCategories->count() > 0;
                        @endphp
                        <div class="border-b border-gray-100 last:border-0">
                            <div class="flex items-center justify-between px-3 py-2.5 hover:bg-gray-50 rounded-lg">
                                <a href="{{ url($mega->slug ?? $mega->id) }}"
                                    class="text-sm font-semibold uppercase text-black flex-1">
                                    {{ $mega->name }}
                                </a>
                                @if ($hasSub)
                                    <button type="button" class="accordion-btn p-1.5 text-black focus:outline-none"
                                        data-target="m3-cat-{{ $mega->id }}">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                @endif
                            </div>

                            @if ($hasSub)
                                <div id="m3-cat-{{ $mega->id }}" class="hidden bg-gray-50 rounded-lg mb-1 border-t border-gray-100">
                                    @foreach ($mega->subCategories as $sub)
                                        @php
                                            $hasMini = $sub->miniCategories && $sub->miniCategories->count() > 0;
                                        @endphp
                                        <div class="border-b border-gray-200/60 last:border-0">
                                            <div class="flex items-center justify-between pl-6 pr-3 py-2">
                                                <a href="{{ url($sub->slug ?? $sub->id) }}"
                                                    class="text-xs font-medium text-black flex-1">
                                                    {{ $sub->name }}
                                                </a>
                                                @if ($hasMini)
                                                    <button type="button" class="accordion-btn p-1 text-black"
                                                        data-target="m3-sub-{{ $sub->id }}">
                                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                                    </button>
                                                @endif
                                            </div>

                                            @if ($hasMini)
                                                <div id="m3-sub-{{ $sub->id }}" class="hidden bg-white pl-8 pr-3 py-1">
                                                    @foreach ($sub->miniCategories as $mini)
                                                        <a href="{{ url($mini->slug ?? $mini->id) }}"
                                                            class="block py-1.5 text-[11px] font-normal text-black hover:text-[var(--primary-color)] border-b border-gray-50 last:border-0 transition-colors">
                                                            {{ $mini->name }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
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
        <div class="text-lg font-bold text-gray-800">Shopping Cart</div>
        <button onclick="toggleCartDrawer()" aria-label="Close cart drawer" class="text-gray-500 hover:text-red-500 text-2xl">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Products List -->
    <div class="flex-1 overflow-y-auto p-4" id="mini-cart-list">
        <!-- কম্পোনেন্ট কল করা হলো -->
        <x-template1.cart-drawer-items />
    </div>

    <!-- Footer -->
    <div class="p-4 border-t bg-gray-50" id="mini-cart-footer" style="{{ \Gloudemans\Shoppingcart\Facades\Cart::count() > 0 ? '' : 'display:none;' }}">
        <div class="flex justify-between items-center mb-4">
            <span class="text-lg font-bold text-gray-700">SUBTOTAL:</span>
            <span class="text-lg font-bold text-gray-900" id="mini-cart-subtotal-val" data-subtotal-raw="{{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal(0, '', '') }}">
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
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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

            input.addEventListener('input', function () {
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
    window.addEventListener('click', function (e) {
        const wrapper = document.getElementById('desktop-account-wrapper');
        const menu = document.getElementById('desktop-account-menu');
        const chevron = document.getElementById('account-chevron');

        if (wrapper && !wrapper.contains(e.target)) {
            if (menu) menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
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
    });
</script>