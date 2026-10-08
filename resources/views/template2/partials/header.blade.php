@php
    $customMenu = \App\Models\MenuSetting::where('company_id', $setup->company_id ?? null)
        ->where('status', \App\Enums\Status::Active->value)
        ->where('type', 'menu')
        ->first();

    $menuItems = $customMenu ? $customMenu->items : null;
@endphp
<header class="w-full header-custom-bg relative">
    <!-- 1. Main Header -->
    <div class="container mx-auto px-4 py-3 md:py-4">
        <div class="flex items-center justify-between gap-4 lg:gap-8">

            <!-- Mobile Menu Toggle (Visible only on Mobile) -->
            <button onclick="toggleMobileMenu()" aria-label="Open mobile menu" class="md:hidden text-header text-2xl focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex-shrink-0">
                @if ($setup && $setup->logo)
                    <img src="{{ $setup->logo_url ?? asset('images/logo.jpeg') }}" alt="{{ $setup->shop_name }}" width="160" height="56"
                        class="h-8 sm:h-10 md:h-14 w-auto">
                @else
                    <span class="text-xl md:text-2xl font-bold text-header">{{ $setup->shop_name ?? 'Shop Name' }}</span>
                @endif
            </a>

            <!-- Segmented Search Bar with Suggestions -->
            <form action="{{ route('shop.index') }}" method="GET" class="hidden md:flex flex-1 max-w-2xl relative"
                id="header-search-container">
                <div
                    class="flex w-full border border-[var(--primary-color)] rounded-sm overflow-hidden bg-white z-30 relative">
                    <!-- Category Dropdown -->
                    <div class="relative flex-shrink-0 border-r border-[var(--primary-color)] w-[130px] max-w-[200px]">
                        <select name="category" id="header-category-select" aria-label="Select Category"
                            class="w-full h-full pl-3 pr-8 py-2 text-sm md:text-base text-header  bg-transparent outline-none appearance-none cursor-pointer">
                            <option value="">All</option>
                            @foreach ($headerCategories as $cat)
                                <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
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
                        class="primary-bg text-primary px-8 py-2 text-lg   hover:bg-opacity-95 transition-colors">Search</button>
                </div>

                <!-- Search Suggestions Dropdown -->
                <div id="search-suggestions"
                    class="hidden absolute top-full left-0 w-full bg-white mt-1 rounded-b-xl shadow-2xl border border-gray-100 z-50 overflow-hidden pt-2">

                    <div id="suggestion-content">
                        <div class="pb-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Popular
                                Search</p>
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
                            <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Trending
                                products</p>
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

                    <!-- লাইভ Search রেজাল্ট (টাইপ করলে এখানে দেখাবে) -->
                    <div id="live-search-results" class="hidden py-2 border-t border-gray-50"></div>
                </div>
            </form>

            <!-- Right Side Actions (Cart & Account) -->
            <div class="flex items-center gap-3 sm:gap-4 lg:gap-6">
                <!-- Cart Icon -->
                <button onclick="toggleCartDrawer()" aria-label="Open cart drawer" class="relative group outline-none">
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

                <!-- Account Icon -->
                <div class="relative group hidden md:block" id="desktop-account-wrapper">
                    @auth('customer')
                        <!-- Logged In User Trigger -->
                        <button onclick="toggleDesktopAccount()" aria-label="Toggle account menu"
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
                                <p class="text-xs text-gray-400">Logged In</p>
                                <p class="text-sm font-bold text-gray-800 truncate">{{ auth('customer')->user()->email }}
                                </p>
                            </div>

                            <a href="{{ route('user.dashboard') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-header transition-colors">
                                <i class="fas fa-th-large w-4 text-gray-400"></i> Dashboard
                            </a>
                            <a href="{{ route('user.dashboard') }}?section=orders"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-header transition-colors">
                                <i class="fas fa-box w-4 text-gray-400"></i> My Orders
                            </a>
                            <a href="{{ route('user.profile') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-header transition-colors">
                                <i class="fas fa-user-edit w-4 text-gray-400"></i> Update Profile
                            </a>

                            <div class="border-t border-gray-50 mt-2 pt-1">
                                <form action="{{ route('user.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-red-500 hover:bg-red-50 transition-colors">
                                        <i class="fas fa-sign-out-alt w-4"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest User Icon -->
                        <a href="{{ route('user.login') }}" aria-label="Login" class="flex items-center gap-2 text-header group/login">
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
                    <span>Call Us</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Search Bar (Updated with Segment and Suggestions) -->
    <div class="md:hidden px-4 pb-4 relative" id="mobile-search-container">
        <form action="{{ route('shop.index') }}" method="GET"
            class="flex border border-[var(--primary-color)] rounded-sm overflow-hidden bg-white">
            <!-- Category Segment for Mobile -->
            <div
                class="relative flex-shrink-0 border-r border-[var(--primary-color)] bg-gray-50 w-[100px] flex items-center">
                <select name="category" id="mobile-category-select" aria-label="Select Category"
                    class="w-full pl-2 pr-6 py-2 text-xs text-header font-semibold bg-transparent outline-none truncate appearance-none cursor-pointer">
                    <option value="">All</option>
                    @foreach ($headerCategories as $cat)
                        <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-1 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-[10px] text-header"></i>
                </div>
            </div>

            <input type="text" name="search" id="mobile-search-input" autocomplete="off" value="{{ request('search') }}"
                placeholder="Find some product ..." class="flex-1 px-3 py-2 text-sm outline-none">

            <button type="submit" aria-label="Search" class="primary-bg text-primary px-4 py-2 font-bold text-sm">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <!-- Mobile Search Suggestions -->
        <div id="mobile-search-suggestions"
            class="hidden absolute top-full left-4 right-4 bg-white mt-1 rounded-b-lg shadow-2xl border border-gray-100 z-[3500] overflow-hidden pt-2">
            <div id="mobile-suggestion-content">
                <!-- Popular and trending data will be same as desktop -->
                <div class="pb-2">
                    <p class="text-[9px] font-bold text-gray-400 uppercase px-4 py-2">Popular Searches</p>
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
    <style>
        /* Ensure category dropdowns show reliably on desktop hover */
        #desktop-bottom-nav .nav-dropdown-item:hover>.nav-dropdown-menu {
            display: block !important;
        }

        #desktop-bottom-nav .nav-sub-item:hover>.nav-sub-dropdown-menu {
            display: block !important;
        }
    </style>
    <nav class="primary-bg hidden md:block transition-all duration-300 w-full relative z-40" id="desktop-bottom-nav">
        <div class="container mx-auto">
            <ul
                class="flex items-center justify-center text-primary text-sm lg:text-base font-medium flex-wrap lg:flex-nowrap">
                @if ($menuItems && count($menuItems) > 0)
                    @foreach (collect($menuItems)->sortBy('order') as $item)
                        @if (data_get($item, 'visible') === true)
                            @php
                                $link = $item['link'] ?? '#';
                                $finalUrl = str_starts_with($link, 'http') ? $link : url($link);
                            @endphp
                            <li class="nav-dropdown-item group relative flex-shrink-0">
                                <a href="{{ $finalUrl }}"
                                    class="flex items-center gap-1.5 px-4 py-3 group-hover:bg-[var(--secondary-color,#000000)] group-hover:text-[var(--secondary-text,#ffffff)] hover:bg-[var(--secondary-color,#000000)] hover:text-[var(--secondary-text,#ffffff)] transition-colors font-medium border-r border-white/20 whitespace-nowrap uppercase">
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
                                                        class="nav-sub-item group/sub  hover:bg-gray-50 flex justify-between items-center cursor-pointer relative border-b border-gray-50 last:border-0">
                                                        <a href="{{ $childUrl }}"
                                                            class="group-hover/sub:text-[var(--primary-color,#016738)] px-4 py-2.5 text-gray-700 uppercase flex-1 text-xs md:text-sm font-semibold transition-colors">
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
                            class="flex items-center px-4 py-3 hover:bg-[var(--secondary-color,#000000)] hover:text-[var(--secondary-text,#ffffff)] transition-colors font-medium border-r border-white/20 whitespace-nowrap uppercase">
                            Home
                        </a>
                    </li>
                    <li class="flex-shrink-0">
                        <a href="{{ route('flash.sale') }}"
                            class="flex items-center px-4 py-3 hover:bg-[var(--secondary-color,#000000)] hover:text-[var(--secondary-text,#ffffff)] transition-colors font-medium border-r border-white/20 whitespace-nowrap uppercase">
                            Offers
                        </a>
                    </li>
                    @foreach ($headerCategories->take(9) as $mega)
                        @php
                            $hasSub = $mega->subCategories && $mega->subCategories->count() > 0;
                        @endphp
                        <li class="nav-dropdown-item group relative flex-shrink-0">
                            <a href="{{ url($mega->slug ?? $mega->id) }}"
                                class="flex items-center gap-1.5 px-4 py-3 group-hover:bg-[var(--secondary-color,#000000)] group-hover:text-[var(--secondary-text,#ffffff)] hover:bg-[var(--secondary-color,#000000)] hover:text-[var(--secondary-text,#ffffff)] transition-colors font-medium border-r border-white/20 whitespace-nowrap uppercase">
                                <span>{{ $mega->name }}</span>
                                @if ($hasSub)
                                    <i
                                        class="fa-solid fa-chevron-down text-[10px] opacity-70 group-hover:rotate-180 transition-transform duration-200"></i>
                                @endif
                            </a>

                            @if ($hasSub)
                                <div class="nav-dropdown-menu absolute left-0 top-full hidden group-hover:block z-[100] pt-1">
                                    <ul
                                        class="relative w-64 bg-white shadow-2xl border border-gray-100 py-2 rounded-b-md text-gray-700 text-sm font-medium">
                                       @foreach ($mega->subCategories->sortBy(fn($sub) => strtolower($sub->name)) as $sub)
                                            @php
                                                $hasMini = $sub->miniCategories && $sub->miniCategories->count() > 0;
                                            @endphp
                                            <li
                                                class="nav-sub-item group/sub hover:bg-gray-50 flex justify-between items-center cursor-pointer relative border-b border-gray-50 last:border-0">
                                                <a href="{{ url($sub->slug ?? $sub->id) }}"
                                                    class="group-hover/sub:text-[var(--primary-color,#016738)] px-4 py-2.5  text-gray-700 uppercase flex-1 text-xs md:text-sm font-medium transition-colors">
                                                    {{ $sub->name }}
                                                </a>
                                                @if ($hasMini)
                                                    <i
                                                        class="fa-solid fa-chevron-right text-xs text-gray-400 pr-[10px] group-hover/sub:text-[var(--primary-color,#016738)] group-hover/sub:translate-x-0.5 transition-transform"></i>
                                                @endif

                                                @if ($hasMini)
                                                    <ul
                                                        class="nav-sub-dropdown-menu absolute {{ (isset($loop->parent) && $loop->parent->remaining < 2) ? 'right-full border-r' : 'left-full border-l' }} top-0 w-full bg-white shadow-2xl border-gray-100 py-2 hidden group-hover/sub:block rounded-md">
                                                       @foreach ($sub->miniCategories->sortBy(fn($mini) => strtolower($mini->name)) as $mini)
                                                            <li class=" hover:bg-gray-100 border-b border-gray-50 last:border-0">
                                                                <a href="{{ url($mini->slug ?? $mini->id) }}"
                                                                    class="px-4 py-2 block hover:text-[var(--primary-color,#016738)] text-gray-600 hover:text-gray-900 uppercase text-[14px] font-medium transition-colors">
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
                @endif
            </ul>
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
                <img src="{{ $setup->logo_url ?? asset('images/logo.jpeg') }}" alt="{{ $setup->shop_name }}" width="160" height="56"
                    class="h-8 w-auto">
            @else
                <span class="text-xl font-bold text-black">{{ $setup->shop_name ?? 'Shop Name' }}</span>
            @endif
            <button onclick="toggleMobileMenu()" aria-label="Close mobile menu" class="text-black text-2xl focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- User Brief Info -->
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
                                        data-target="mobile-m2-{{ $loop->index }}">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                @endif
                            </div>

                            @if ($hasChildren)
                                <div id="mobile-m2-{{ $loop->index }}"
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
                                                        class="text-[14px] font-medium text-black flex-1 hover:text-[var(--primary-color)] transition-colors">
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
                    class="block px-3 py-2 text-sm font-semibold uppercase text-black hover:bg-gray-50 rounded-lg transition-colors">Home</a>
                <a href="{{ route('flash.sale') }}"
                    class="block px-3 py-2 text-sm font-semibold uppercase text-black hover:bg-gray-50 rounded-lg transition-colors">Offers</a>

                <div class="pt-2 mt-2 border-t border-gray-100">
                    <p class="px-3 py-1.5 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Categories</p>
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
                                        data-target="m2-cat-{{ $mega->id }}">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                @endif
                            </div>

                            @if ($hasSub)
                                <div id="m2-cat-{{ $mega->id }}" class="hidden bg-gray-50 rounded-lg mb-1 border-t border-gray-100">
                                    @foreach ($mega->subCategories as $sub)
                                        @php
                                            $hasMini = $sub->miniCategories && $sub->miniCategories->count() > 0;
                                        @endphp
                                        <div class="border-b border-gray-200/60 last:border-0">
                                            <div class="flex items-center justify-between pl-6 pr-3 py-2">
                                                <a href="{{ url($sub->slug ?? $sub->id) }}"
                                                    class="text-[14px] font-medium text-black flex-1">
                                                    {{ $sub->name }}
                                                </a>
                                                @if ($hasMini)
                                                    <button type="button" class="accordion-btn p-1 text-black"
                                                        data-target="m2-sub-{{ $sub->id }}">
                                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                                    </button>
                                                @endif
                                            </div>

                                            @if ($hasMini)
                                                <div id="m2-sub-{{ $sub->id }}" class="hidden bg-white pl-8 pr-3 py-1">
                                                    @foreach ($sub->miniCategories as $mini)
                                                        <a href="{{ url($mini->slug ?? $mini->id) }}"
                                                            class="block py-1.5 text-[14px] font-normal text-black hover:text-black border-b border-gray-50 last:border-0">
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
                    <span>My Dashboard</span>
                </a>

                <form action="{{ route('user.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-center text-red-500 font-bold text-sm py-2 hover:underline">
                        Logout
                    </button>
                </form>
            @else
                <!-- Login / Register বাটন (লগইন না থাকলে) -->
                <a href="{{ route('user.login') }}"
                    class="flex items-center justify-center gap-3 primary-bg text-primary py-3 rounded shadow-sm font-bold text-base hover:bg-opacity-95 transition-all">
                    <i class="fas fa-user-circle text-xl"></i>
                    <span>Login / Register</span>
                </a>
            @endauth

            <!-- Call Us বাটন (সব সময় থাকবে) -->
            <a href="tel:{{ $setup->phone ?? '' }}"
                class="flex items-center justify-center gap-3 primary-bg text-primary py-3 rounded shadow-sm font-bold text-base hover:bg-opacity-95 transition-all">
                <i class="fas fa-phone-alt"></i>
                <span>Call Us</span>
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
        <!-- Component called -->
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
            const categorySelect = document.getElementById(categorySelectId); // Category select ID
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
                const selectedCategory = categorySelect.value; // Get currently selected category

                clearTimeout(debounceTimer);
                if (abortController) abortController.abort();

                if (query.length > 1) {
                    debounceTimer = setTimeout(() => {
                        abortController = new AbortController();
                        defaultContent.classList.add('hidden');
                        liveResults.classList.remove('hidden');
                        liveResults.innerHTML =
                            '<div class="px-5 py-3 text-xs text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Searching...</div>';

                        // Sending category slug as parameter
                        fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}&category=${selectedCategory}`, {
                            signal: abortController.signal
                        })
                            .then(res => res.json())
                            .then(data => {
                                liveResults.innerHTML = '';
                                if (data.length > 0) {
                                    data.forEach(item => {
                                        const link = document.createElement('a');
                                        link.href = "{{ url('') }}/" + item.slug;
                                        link.className =
                                            "flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors";
                                        link.innerHTML = `
                                            <img src="${item.thumbnail_95_url}" class="w-6 h-6 rounded object-cover border" onerror="this.src='{{ asset('images/no-image.png') }}'" loading="lazy" width="800" height="800">
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
                                if (err.name !== 'AbortError') console.error(err);
                            });
                    }, 500);
                } else {
                    defaultContent.classList.remove('hidden');
                    liveResults.classList.add('hidden');
                }
            });

            // Close when clicked outside
            document.addEventListener('click', (e) => {
                if (container && !container.contains(e.target)) {
                    suggestionBox.classList.add('hidden');
                }
            });
        }

        // ডেক্সটপ Search অ্যাক্টিভেট
        setupSearch('header-search-input', 'header-category-select', 'search-suggestions', 'suggestion-content',
            'live-search-results', 'header-search-container');

        // মোবাইল Search অ্যাক্টিভেট
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

    // Sticky Desktop Navigation on Scroll
    document.addEventListener('DOMContentLoaded', function () {
        const bottomNav = document.getElementById('desktop-bottom-nav');
        if (bottomNav) {
            // Create a wrapper to prevent layout jump
            const wrapper = document.createElement('div');
            wrapper.className = 'hidden md:block w-full';
            bottomNav.parentNode.insertBefore(wrapper, bottomNav);
            wrapper.appendChild(bottomNav);

            // Inject animation styles
            if (!document.getElementById('sticky-nav-style')) {
                const style = document.createElement('style');
                style.id = 'sticky-nav-style';
                style.innerHTML = `
                    @keyframes slideDownNav {
                        from { transform: translateY(-100%); }
                        to { transform: translateY(0); }
                    }
                    .smooth-sticky-nav {
                        position: fixed !important;
                        top: 0;
                        left: 0;
                        right: 0;
                        z-index: 50;
                        animation: slideDownNav 0.35s ease-in-out;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    }
                `;
                document.head.appendChild(style);
            }

            window.addEventListener('scroll', function () {
                if (window.scrollY > 150) {
                    if (!bottomNav.classList.contains('smooth-sticky-nav')) {
                        wrapper.style.height = bottomNav.offsetHeight + 'px';
                        bottomNav.classList.add('smooth-sticky-nav');
                    }
                } else {
                    if (bottomNav.classList.contains('smooth-sticky-nav')) {
                        bottomNav.classList.remove('smooth-sticky-nav');
                        wrapper.style.height = 'auto';
                    }
                }
            });
        }

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