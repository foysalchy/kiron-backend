@php
    $customMenu = \App\Models\MenuSetting::where('company_id', $setup->company_id ?? null)
        ->where('status', \App\Enums\Status::Active->value)
        ->first();

    $menuItems = $customMenu ? $customMenu->items : null;
@endphp
<header class="w-full sticky top-0 z-50">
    <div class="hidden md:block bg-[#3533cd] text-header py-2 px-4 md:px-10">
        <div class="container mx-auto flex justify-between items-center text-xs md:text-sm">
            <div class="flex items-center gap-2">
                <!-- WhatsApp Icon with label -->
                <i class="fa-brands fa-whatsapp text-header text-lg" aria-hidden="true"></i>
                <span>Call Or Text Us to Order :
                    <a href="tel:{{ $setup->phone ?? '+88000000000' }}" class="hover:underline" aria-label="Call us">
                        {{ $setup->phone ?? '+880 00000000' }}
                    </a>
                </span>
            </div>

            <!-- Desktop Top Bar login/logout section -->
            @auth('customer')
                <div class="flex items-center gap-4">
                    <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2 hover:opacity-80 transition">
                        <i class="fa-regular fa-circle-user"></i>
                        <span>{{ auth('customer')->user()->name }}</span>
                    </a>
                    <form action="{{ route('user.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-bold hover:text-red-300 transition cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket"></i> LOGOUT
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('user.login') }}" class="flex items-center gap-2 hover:opacity-80 transition">
                    <i class="fa-regular fa-user"></i>
                    <span>Log In / Register</span>
                </a>
            @endauth
        </div>
    </div>

    <div class="header-custom-bg text-header py-4 px-4 md:px-10  ">
        <div class="container mx-auto flex items-center justify-between gap-4">
            <button id="menu-toggle" aria-label="Open Menu" class="lg:hidden text-2xl focus:outline-none"
                aria-label="Open navigation menu" aria-expanded="false" aria-controls="mobile-sidebar">
                <i class="fa-solid fa-bars-staggered" aria-hidden="true"></i>
            </button>

            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" aria-label="Little Joy Baby Shop Home">
                    <img src="{{ $setup->logo_url ?? asset('images/logo.jpeg') }}" height="80" width="200"
                        alt="{{ $setup->shop_name ?? 'Little Joy Baby Shop' }} Logo"
                        class="h-16 md:h-16 w-auto object-contain" />
                </a>
            </div>

            <form action="{{ route('shop.index') }}" method="GET"
                class="hidden lg:block flex-1 max-w-3xl mx-10 relative" id="header-search-container">
                <div class="relative z-30">
                    <input type="text" name="search" id="header-search-input" autocomplete="off"
                        placeholder="Search by product name" aria-label="Search for baby products"
                        class="w-full py-3 px-6 rounded-full text-gray-700 focus:outline-none bg-white placeholder-gray-400 text-sm border border-gray-100 shadow-sm" />
                    <button type="submit" aria-label="Search Products"
                        class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-600 hover:text-header">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </button>
                </div>

                <!-- Suggestions Dropdown -->
                <div id="search-suggestions"
                    class="hidden absolute top-[90%] left-0 w-full bg-white mt-1 rounded-b-2xl shadow-2xl border border-gray-100 z-20 overflow-hidden pt-4 pb-2">
                    <div id="suggestion-content">
                        <div class="pb-2">
                            <p class="text-[10px] font-bold text-gray-600 uppercase px-5 py-2 tracking-wider">Popular
                                Searches</p>
                            @foreach ($popularSearches as $item)
                                <a href="{{ route('shop.index', ['search' => $item->keyword]) }}"
                                    class="flex items-center justify-between px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-history text-gray-300 text-xs"></i>
                                        <span>{{ $item->keyword }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <div class="border-t border-gray-50 pt-2 pb-2">
                            <p class="text-[10px] font-bold text-gray-600 uppercase px-5 py-2 tracking-wider">Trending
                                Products</p>
                            @foreach ($relatedProducts as $p)
                                <a href="{{ route('product.details', $p->slug) }}"
                                    class="flex items-center gap-3 px-5 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <img src="{{ $p->thumbnail_url }}" height="" width=""
                                        alt="product iamge" class="w-8 h-8 rounded object-cover border border-gray-100">
                                    <span class="truncate">{{ $p->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <!-- Live Results (typed by user) -->
                    <div id="live-search-results" class="hidden py-2 border-t border-gray-50"></div>
                </div>
            </form>

            <div class="flex items-center gap-4 md:gap-8">
                <!-- Wishlist Link -->
                <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 cursor-pointer group"
                    aria-label="View your wishlist, currently 0 items">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-full flex items-center justify-center">
                        <i class="fa-regular fa-heart text-xl text-[var(--primary-color)]" aria-hidden="true"></i>
                    </div>
                    <div class="hidden xl:block">
                        <p class="text-sm font-medium leading-tight">My Wishlist</p>
                        <p class="text-xs opacity-80">
                            ( @auth('customer')
                                <span class="wishlist-count-val">
                                    {{ auth('customer')->check() ? \App\Models\Wishlist::where('customer_id', auth('customer')->id())->count() : 0 }}
                                </span>
                            @else
                                0
                            @endauth items )
                        </p>
                    </div>
                </a>

                <!-- Shopping Cart Link -->
                <a href="{{ route('cart.index') }}" class="flex items-center gap-3 cursor-pointer group">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-bag-shopping text-xl text-[var(--primary-color)]"></i>
                    </div>

                    <div class="hidden lg:block">
                        <p class="text-sm font-medium leading-tight">Shopping Card</p>
                        <p class="text-xs opacity-80">
                            ( <span class="cart-count-nav">{{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}</span>
                            items )
                        </p>
                    </div>
                </a>
            </div>
        </div>

        <form action="{{ route('shop.index') }}" method="GET" class="lg:hidden mt-4 relative"
            id="mobile-search-container">
            <div class="relative z-30">
                <input type="text" name="search" id="mobile-search-input" autocomplete="off"
                    placeholder="Search..." aria-label="Search products"
                    class="w-full py-2 px-5 rounded-full text-gray-700 focus:outline-none bg-white border border-gray-100" />
                <button type="submit" aria-label="Search"
                    class="absolute right-1 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center text-gray-600 hover:text-header transition-colors">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </button>
            </div>

            <!-- Mobile Suggestions Dropdown -->
            <div id="mobile-search-suggestions"
                class="hidden absolute top-[90%] left-0 w-full bg-white mt-1 rounded-b-xl shadow-2xl border border-gray-100 z-[100] overflow-hidden pt-4 pb-2">
                <div id="mobile-suggestion-content">
                    <p class="text-[10px] font-bold text-gray-600 uppercase px-5 py-2 tracking-wider">Popular Searches
                    </p>
                    @foreach ($popularSearches as $item)
                        <a href="{{ route('shop.index', ['search' => $item->keyword]) }}"
                            class="block px-5 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ $item->keyword }}</a>
                    @endforeach
                </div>
                <div id="mobile-live-search-results" class="hidden py-2 border-t border-gray-50"></div>
            </div>
        </form>
    </div>

    <nav class="hidden lg:block shadow-sm bg-white border-b border-gray-100">
        <div class="container mx-auto px-4 md:px-10">
            <ul class="flex items-center justify-center text-sm font-semibold text-gray-700">
                @if ($menuItems && count($menuItems) > 0)
                    @foreach (collect($menuItems)->sortBy('order') as $item)
                        @if (data_get($item, 'visible') === true)
                            @php
                                $link = $item['link'] ?? '#';
                                $finalUrl = str_starts_with($link, 'http') ? $link : url($link);
                            @endphp
                            <li class="group relative">
                                <a href="{{ $finalUrl }}"
                                    class="flex items-center gap-2 px-5 py-4 hover:text-header transition-all cursor-pointer uppercase">
                                    {{ $item['label'] }}
                                    @if (!empty($item['children']))
                                        <i class="fa-solid fa-chevron-down text-[10px] mt-1 opacity-50"></i>
                                    @endif
                                </a>

                                @if (!empty($item['children']))
                                    <div class="absolute left-0 top-full hidden group-hover:block z-[100] pt-1">
                                        <ul class="relative w-64 bg-white shadow-2xl border border-gray-100 py-2">
                                            @foreach ($item['children'] as $child)
                                                @if (data_get($child, 'visible') === true)
                                                    @php
                                                        $cLink = $child['link'] ?? '#';
                                                        $cUrl = str_starts_with($cLink, 'http') ? $cLink : url($cLink);
                                                    @endphp
                                                    <li class="px-4 py-2.5 hover:bg-gray-100">
                                                        <a href="{{ $cUrl }}"
                                                            class="group-hover:text-header uppercase block">
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
                    @foreach ($headerCategories->take(5) as $mega)
                        <li class="group relative">
                            <a href="{{ route('category.products', $mega->slug ?? $mega->id) }}"
                                class="flex items-center gap-2 px-5 py-4 hover:text-header transition-all cursor-pointer uppercase">
                                {{ $mega->name }}
                                @if ($mega->subCategories->count() > 0)
                                    <i class="fa-solid fa-chevron-down text-[10px] mt-1 opacity-50"></i>
                                @endif
                            </a>

                            @if ($mega->subCategories->count() > 0)
                                <div class="absolute left-0 top-full hidden group-hover:block z-[100] pt-1">
                                    <ul class="relative w-64 bg-white shadow-2xl border border-gray-100 py-2">
                                        @foreach ($mega->subCategories as $sub)
                                            <li
                                                class="group/sub px-4 py-2.5 hover:bg-gray-100 flex justify-between items-center cursor-pointer">
                                                <a href="{{ route('category.products', $sub->slug ?? $sub->id) }}"
                                                    class="group-hover/sub:text-header uppercase">
                                                    {{ $sub->name }}
                                                </a>
                                                @if ($sub->miniCategories->count() > 0)
                                                    <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>
                                                @endif

                                                @if ($sub->miniCategories->count() > 0)
                                                    <ul
                                                        class="absolute left-full top-0 w-64 min-h-full bg-white shadow-2xl border-l border-gray-100 py-2 hidden group-hover/sub:block">
                                                        @foreach ($sub->miniCategories as $mini)
                                                            <li class="px-4 py-2.5 hover:bg-gray-100">
                                                                <a href="{{ route('category.products', $mini->slug ?? $mini->id) }}"
                                                                    class="block group-hover/mini:text-header uppercase">
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

    <div id="mobile-sidebar"
        class="fixed inset-y-0 left-0 w-80 bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out z-[60] flex flex-col">

        <div class="p-4 flex justify-between items-center border-b header-custom-bg text-header">
            <h2 class="font-bold text-lg uppercase tracking-wider">All Categories</h2>
            <button id="close-sidebar" aria-label="Close Menu"
                class="text-2xl hover:text-red-400 transition-colors">&times;</button>
        </div>

        <nav class="flex-1 overflow-y-auto ">
            @if ($menuItems && count($menuItems) > 0)
                @foreach (collect($menuItems)->sortBy('order') as $index => $item)
                    @if (data_get($item, 'visible') === true)
                        @php
                            $link = $item['link'] ?? '#';
                            $finalUrl = str_starts_with($link, 'http') ? $link : url($link);
                        @endphp
                        <div class="border-b border-[#f3f3f3]">
                            <div class="flex justify-between items-center px-5 py-4 group">
                                <a href="{{ $finalUrl }}"
                                    class="text-[#0f172a] font-bold uppercase text-sm flex-1">
                                    {{ $item['label'] }}
                                </a>

                                @if (!empty($item['children']))
                                    <button class="accordion-btn text-gray-400 p-2 -mr-2"
                                        data-target="m-custom-{{ $index }}">
                                        <i class="fa-solid fa-plus text-xs transition-transform duration-300"></i>
                                    </button>
                                @endif
                            </div>

                            {{-- কাস্টম মেনুর সাব-মেনু (Children) --}}
                            @if (!empty($item['children']))
                                <div id="m-custom-{{ $index }}"
                                    class="hidden bg-gray-50 border-t border-gray-100">
                                    @foreach ($item['children'] as $child)
                                        @if (data_get($child, 'visible') === true)
                                            @php
                                                $cLink = $child['link'] ?? '#';
                                                $cUrl = str_starts_with($cLink, 'http') ? $cLink : url($cLink);
                                            @endphp
                                            <a href="{{ $cUrl }}"
                                                class="block px-10 py-3 text-sm border-b border-[#f3f3f3] text-gray-700">
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
                @foreach ($headerCategories as $mega)
                    <div class="border-b border-[#f3f3f3]">
                        <div class="flex justify-between items-center px-5 py-4 group">
                            <a href="{{ route('category.products', $mega->slug) }}"
                                class="text-[#0f172a] font-bold uppercase text-sm group-hover:text-header transition-colors flex-1">
                                {{ $mega->name }}
                            </a>

                            @if ($mega->subCategories->count() > 0)
                                <button class="accordion-btn text-gray-400 p-2 -mr-2"
                                    aria-label="Toggle {{ $mega->name }} categories"
                                    data-target="m-cat-{{ $mega->id }}">
                                    <i class="fa-solid fa-plus text-xs transition-transform duration-300"></i>
                                </button>
                            @endif
                        </div>

                        @if ($mega->subCategories->count() > 0)
                            <div id="m-cat-{{ $mega->id }}" class="hidden bg-gray-50 border-t border-gray-100">
                                @foreach ($mega->subCategories as $sub)
                                    <div class="border-b border-gray-100 last:border-0">
                                        <div class="flex justify-between items-center pl-8 pr-5 py-3">
                                            <a href="{{ route('category.products', $sub->slug) }}"
                                                class="text-sm font-semibold text-gray-700 hover:text-header flex-1">
                                                {{ $sub->name }}
                                            </a>

                                            @if ($sub->miniCategories->count() > 0)
                                                <button class="accordion-btn text-gray-400 p-1"
                                                    data-target="m-sub-{{ $sub->id }}">
                                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                                </button>
                                            @endif
                                        </div>

                                        {{-- ৩. মিনি ক্যাটাগরি লিস্ট --}}
                                        @if ($sub->miniCategories->count() > 0)
                                            <div id="m-sub-{{ $sub->id }}" class="hidden bg-gray-100/50">
                                                @foreach ($sub->miniCategories as $mini)
                                                    <a href="{{ route('category.products', $mini->slug) }}"
                                                        class="block pl-12 pr-5 py-2.5 text-xs font-medium text-gray-600 border-b border-gray-50 last:border-0 hover:text-header">
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
            @endif
            <div class="p-4 space-y-2 bg-white mt-4">
                @auth('customer')
                    <div class="p-4 bg-purple-50 rounded-xl mb-2">
                        <p class="text-[10px] text-purple-600 uppercase font-black tracking-widest mb-1">Welcome back,</p>
                        <p class="font-bold text-gray-900">{{ auth('customer')->user()->name }}</p>
                    </div>
                    <a href="{{ route('user.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 font-bold hover:bg-gray-50 rounded-lg transition-all">
                        <i class="fa-regular fa-circle-user text-lg text-header"></i>
                        <span>My Dashboard</span>
                    </a>
                    <form action="{{ route('user.logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-500 font-bold hover:bg-red-50 rounded-lg transition-all text-left">
                            <i class="fa-solid fa-right-from-bracket text-lg"></i>
                            <span>Sign Out</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('user.login') }}"
                        class="flex items-center justify-center p-4 header-custom-bg text-header rounded-xl font-bold shadow-lg shadow-purple-100 active:scale-95 transition-all">
                        <i class="fa-regular fa-user mr-2"></i> Log In / Register
                    </a>
                @endauth

                <a href="tel:{{ $setup->phone ?? '' }}"
                    class="flex items-center justify-center gap-2 p-4 border-2 border-green-500 text-green-600 rounded-xl font-bold mt-4">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                    <span>Order on WhatsApp</span>
                </a>
            </div>
        </nav>
    </div>
    <div id="overlay" class="fixed inset-0 bg-black/50 hidden z-[55]" aria-hidden="true"></div>
</header>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchConfigs = [{
                input: document.getElementById('header-search-input'),
                suggestions: document.getElementById('search-suggestions'),
                results: document.getElementById('live-search-results'),
                defaultContent: document.getElementById('suggestion-content'),
            },
            {
                input: document.getElementById('mobile-search-input'),
                suggestions: document.getElementById('mobile-search-suggestions'),
                results: document.getElementById('mobile-live-search-results'),
                defaultContent: document.getElementById('mobile-suggestion-content'),
            }
        ];

        let debounceTimer;

        searchConfigs.forEach(config => {
            if (!config.input) return;

            config.input.addEventListener('focus', () => {
                config.suggestions.classList.remove('hidden');
            });

            config.input.addEventListener('input', function() {
                const query = this.value.trim();
                clearTimeout(debounceTimer);

                if (query.length > 1) {
                    debounceTimer = setTimeout(() => {
                        if (config.defaultContent) config.defaultContent.classList.add(
                            'hidden');
                        config.results.classList.remove('hidden');
                        config.results.innerHTML =
                            '<div class="px-5 py-3 text-xs text-gray-600"><i class="fas fa-spinner fa-spin mr-2"></i>Searching...</div>';

                        fetch(
                                `{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`
                            )
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
                                            "flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-50 last:border-0";
                                        link.innerHTML = `
                                        <img src="${item.thumbnail_url}" height="" width="" class="w-8 h-8 rounded object-cover border border-gray-100" onerror="this.src='{item.thumbnail_url}'">
                                        <span class="truncate">${item.title}</span>
                                    `;
                                        config.results.appendChild(link);
                                    });
                                } else {
                                    config.results.innerHTML =
                                        '<div class="px-5 py-3 text-xs text-gray-600">No products found.</div>';
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
</script>
