<header class="w-full sticky top-0 z-50">
    <div class="hidden md:block bg-[#3533cd] text-white py-2 px-4 md:px-10">
        <div class="container mx-auto flex justify-between items-center text-xs md:text-sm">
            <div class="flex items-center gap-2">
                <!-- WhatsApp Icon with label -->
                <i class="fa-brands fa-whatsapp text-[#4ade80] text-lg" aria-hidden="true"></i>
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

    <div class="bg-[#66267b] text-white py-4 px-4 md:px-10 border-b border-purple-800">
        <div class="container mx-auto flex items-center justify-between gap-4">
            <button id="menu-toggle" class="lg:hidden text-2xl focus:outline-none" aria-label="Open navigation menu"
                aria-expanded="false" aria-controls="mobile-sidebar">
                <i class="fa-solid fa-bars-staggered" aria-hidden="true"></i>
            </button>

            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" aria-label="Little Joy Baby Shop Home">
                    <img src="{{ $setup->logo_url ?? asset('images/babyshop/images/babylogo.png') }}"
                        alt="{{ $setup->shop_name ?? 'Little Joy Baby Shop' }} Logo"
                        class="h-16 md:h-20 w-auto object-contain" />
                </a>
            </div>

            <form action="{{ route('shop.index') }}" method="GET"
                class="hidden lg:block flex-1 max-w-3xl mx-10 relative" id="header-search-container">
                <div class="relative z-30">
                    <input type="text" name="search" id="header-search-input" autocomplete="off"
                        placeholder="Search by product name" aria-label="Search for baby products"
                        class="w-full py-3 px-6 rounded-full text-gray-700 focus:outline-none bg-white placeholder-gray-400 text-sm border border-gray-100 shadow-sm" />
                    <button type="submit"
                        class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-purple-800">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </button>
                </div>

                <!-- Suggestions Dropdown -->
                <div id="search-suggestions"
                    class="hidden absolute top-[90%] left-0 w-full bg-white mt-1 rounded-b-2xl shadow-2xl border border-gray-100 z-20 overflow-hidden pt-4 pb-2">
                    <div id="suggestion-content">
                        <div class="pb-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Popular
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
                            <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Trending
                                Products</p>
                            @foreach ($relatedProducts as $p)
                                <a href="{{ route('product.details', $p->slug) }}"
                                    class="flex items-center gap-3 px-5 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <img src="{{ $p->thumbnail_url }}"
                                        class="w-8 h-8 rounded object-cover border border-gray-100">
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
                        <i class="fa-regular fa-heart text-xl text-[#66267b]" aria-hidden="true"></i>
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
                <a href="{{ route('cart.index') }}" class="flex items-center gap-3 cursor-pointer group"
                    aria-label="View shopping cart, currently 0 items">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-bag-shopping text-xl text-[#66267b]" aria-hidden="true"></i>
                    </div>
                    <div class="hidden xl:block">
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
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            <!-- Mobile Suggestions Dropdown -->
            <div id="mobile-search-suggestions"
                class="hidden absolute top-[90%] left-0 w-full bg-white mt-1 rounded-b-xl shadow-2xl border border-gray-100 z-[100] overflow-hidden pt-4 pb-2">
                <div id="mobile-suggestion-content">
                    <p class="text-[10px] font-bold text-gray-400 uppercase px-5 py-2 tracking-wider">Popular Searches
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

    <nav class="hidden lg:block shadow-sm bg-white border-b border-gray-100 font-manrope">
        <div class="container mx-auto px-4 md:px-10">
            <ul class="flex items-center justify-center text-sm font-semibold text-gray-700">

                @foreach ($headerCategories->take(5) as $mega)
                    <li class="group relative">
                        <a href="{{ route('category.products', $mega->slug ?? $mega->id) }}"
                            class="flex items-center gap-2 px-5 py-4 hover:text-[#66267b] transition-all cursor-pointer uppercase">
                            {{ $mega->name }} <i class="fa-solid fa-chevron-down text-[10px] mt-1 opacity-50"></i>
                        </a>

                        <div class="absolute left-0 top-full hidden group-hover:block z-[100] pt-1">
                            <ul class="relative w-64 bg-white shadow-2xl border border-gray-100 py-2">

                                @if ($mega->subCategories->count() > 0)
                                    @foreach ($mega->subCategories as $sub)
                                        <li
                                            class="group/sub px-4 py-2.5 hover:bg-gray-100 flex justify-between items-center cursor-pointer">
                                            <a href="{{ route('subcategory.products', [$mega->slug ?? $mega->id, $sub->slug ?? $sub->id]) }}"
                                                class="group-hover/sub:text-[#66267b] uppercase">
                                                {{ $sub->name }}
                                            </a>
                                            <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>

                                            <ul
                                                class="absolute left-full top-0 w-64 min-h-full bg-white shadow-2xl border-l border-gray-100 py-2 hidden group-hover/sub:block">
                                                @if ($sub->miniCategories->count() > 0)
                                                    @foreach ($sub->miniCategories as $mini)
                                                        <li
                                                            class="group/mini px-4 py-2.5 hover:bg-gray-100 flex justify-between items-center cursor-pointer">
                                                            <a href="{{ route('minicategory.products', [$mega->slug ?? $mega->id, $sub->slug ?? $sub->id, $mini->slug ?? $mini->id]) }}"
                                                                class="group-hover/mini:text-[#66267b]">
                                                                {{ $mini->name }}
                                                            </a>
                                                            <i
                                                                class="fa-solid fa-chevron-right text-xs text-gray-400"></i>

                                                            <ul
                                                                class="absolute left-full top-0 w-72 min-h-full bg-white shadow-2xl border-l border-gray-100 py-2 hidden group-hover/mini:block">
                                                                @php
                                                                    $miniProds = $allHeaderProducts
                                                                        ->filter(function ($p) use ($mini) {
                                                                            return is_array($p->mini_category_ids) &&
                                                                                in_array(
                                                                                    (int) $mini->id,
                                                                                    array_map(
                                                                                        'intval',
                                                                                        $p->mini_category_ids,
                                                                                    ),
                                                                                );
                                                                        })
                                                                        ->take(6);
                                                                @endphp
                                                                @forelse($miniProds as $product)
                                                                    <li
                                                                        class="px-4 py-2 hover:bg-gray-50 border-b border-gray-50 last:border-0">
                                                                        <a href="{{ route('product.details', $product->slug) }}"
                                                                            class="flex items-center gap-3">
                                                                            <img src="{{ $product->thumbnail_url }}"
                                                                                class="w-10 h-10 object-cover rounded border"
                                                                                alt="">
                                                                            <div class="flex flex-col min-w-0">
                                                                                <span
                                                                                    class="text-xs font-bold text-gray-700 truncate">{{ $product->title }}</span>
                                                                            </div>
                                                                        </a>
                                                                    </li>
                                                                @empty
                                                                    <li
                                                                        class="px-5 py-4 text-center text-xs text-gray-400 italic">
                                                                        No products</li>
                                                                @endforelse
                                                            </ul>
                                                        </li>
                                                    @endforeach
                                                @else
                                                    @php
                                                        $subProds = $allHeaderProducts
                                                            ->filter(function ($p) use ($sub) {
                                                                return is_array($p->sub_category_ids) &&
                                                                    in_array(
                                                                        (int) $sub->id,
                                                                        array_map('intval', $p->sub_category_ids),
                                                                    );
                                                            })
                                                            ->take(6);
                                                    @endphp
                                                    @forelse($subProds as $product)
                                                        <li
                                                            class="px-4 py-2 hover:bg-gray-50 border-b border-gray-50 last:border-0">
                                                            <a href="{{ route('product.details', $product->slug) }}"
                                                                class="flex items-center gap-3">
                                                                <img src="{{ $product->thumbnail_url }}"
                                                                    class="w-10 h-10 object-cover rounded border"
                                                                    alt="">
                                                                <div class="flex flex-col min-w-0">
                                                                    <span
                                                                        class="text-xs font-bold text-gray-700 truncate">{{ $product->title }}</span>

                                                                </div>
                                                            </a>
                                                        </li>
                                                    @empty
                                                        <li class="px-5 py-4 text-center text-xs text-gray-400 italic">
                                                            No products</li>
                                                    @endforelse
                                                @endif
                                            </ul>
                                        </li>
                                    @endforeach
                                @else
                                    @php
                                        $megaProds = $allHeaderProducts
                                            ->filter(function ($p) use ($mega) {
                                                return is_array($p->mega_category_ids) &&
                                                    in_array(
                                                        (int) $mega->id,
                                                        array_map('intval', $p->mega_category_ids),
                                                    );
                                            })
                                            ->take(6);
                                    @endphp
                                    @forelse($megaProds as $product)
                                        <li class="px-4 py-2 hover:bg-gray-50 border-b border-gray-50 last:border-0">
                                            <a href="{{ route('product.details', $product->slug) }}"
                                                class="flex items-center gap-3">
                                                <img src="{{ $product->thumbnail_url }}"
                                                    class="w-10 h-10 object-cover rounded border" alt="">
                                                <div class="flex flex-col min-w-0">
                                                    <span
                                                        class="text-xs font-bold text-gray-700 truncate">{{ $product->title }}</span>

                                                </div>
                                            </a>
                                        </li>
                                    @empty
                                        <li class="px-5 py-4 text-center text-xs text-gray-400 italic">No products
                                        </li>
                                    @endforelse
                                @endif
                            </ul>
                        </div>
                    </li>
                @endforeach

                <li><a href="/about" class="px-5 py-4 block hover:text-[#66267b] transition-colors">About Us</a></li>
                <li><a href="/contact" class="px-5 py-4 block hover:text-[#66267b] transition-colors">Contact</a></li>
            </ul>
        </div>
    </nav>

    <div id="mobile-sidebar"
        class="fixed inset-y-0 left-0 w-80 bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out z-[60] flex flex-col">
        <div class="p-4 flex justify-between items-center border-b bg-[#66267b] text-white">
            <h2 class="font-bold text-lg">All Categories</h2>
            <button id="close-sidebar" class="text-2xl">&times;</button>
        </div>
        <nav class="flex-1 overflow-y-auto font-manrope">
            <!-- Diapering -->
            <div class="border-b border-[#f3f3f3]">
                <button
                    class="accordion-btn w-full flex justify-between items-center px-5 py-4 text-[#0f172a] font-bold"
                    data-target="m-diaper">
                    <span>Diapering</span> <i class="fa-solid fa-plus text-sm"></i>
                </button>
                <div id="m-diaper" class="hidden bg-gray-50">
                    <a href="#" class="block px-10 py-3 text-sm border-b border-[#f3f3f3]">Diapers</a>
                    <a href="#" class="block px-10 py-3 text-sm border-b border-[#f3f3f3]">Wipes</a>
                </div>
            </div>
            <!-- Baby Foods -->
            <div class="border-b border-[#f3f3f3]">
                <button
                    class="accordion-btn w-full flex justify-between items-center px-5 py-4 text-[#0f172a] font-bold"
                    data-target="m-food">
                    <span>Baby Foods</span> <i class="fa-solid fa-plus text-sm"></i>
                </button>
                <div id="m-food" class="hidden bg-gray-50">
                    <a href="#" class="block px-10 py-3 text-sm border-b border-[#f3f3f3]">Formula Milk</a>
                </div>
                <div class="border-t border-gray-100 mt-2">
                    @auth('customer')
                        <a href="{{ route('user.dashboard') }}"
                            class="flex items-center gap-3 px-5 py-4 text-[#0f172a] font-bold hover:bg-gray-50 transition-all">
                            <i class="fa-regular fa-circle-user text-lg text-[#66267b]"></i>
                            <span>Dashboard ({{ auth('customer')->user()->name }})</span>
                        </a>
                        <form action="{{ route('user.logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-3 px-5 py-4 text-red-600 font-bold hover:bg-red-50 transition-all border-t border-gray-50 cursor-pointer">
                                <i class="fa-solid fa-right-from-bracket text-lg"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('user.login') }}"
                            class="flex items-center gap-3 px-5 py-4 text-[#0f172a] font-bold hover:bg-gray-50 transition-all">
                            <i class="fa-regular fa-user text-lg text-[#66267b]"></i>
                            <span>Log In / Register</span>
                        </a>
                    @endauth

                    <a href="tel:{{ $setup->phone ?? '' }}"
                        class="flex items-center gap-3 px-5 py-4 text-[#0f172a] font-bold hover:bg-gray-50 transition-all border-t border-gray-50">
                        <i class="fa-brands fa-whatsapp text-lg text-green-500"></i>
                        <span>Call: {{ $setup->phone ?? '' }}</span>
                    </a>
                </div>
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
                            '<div class="px-5 py-3 text-xs text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Searching...</div>';

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
                                        <img src="${item.thumbnail_url}" class="w-8 h-8 rounded object-cover border border-gray-100" onerror="this.src='/images/no-image.png'">
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
</script>
