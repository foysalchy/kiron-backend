@php
    $customMenu = \App\Models\MenuSetting::where('company_id', $setup->company_id ?? null)
        ->where('status', \App\Enums\Status::Active->value)
        ->where('type', 'menu')
        ->first();

    $menuItems = $customMenu ? $customMenu->items : null;
@endphp
<header class="w-full ">


    <div class="header-custom-bg text-header py-4 px-4 md:px-10 ">
        <div class="container mx-auto flex items-center justify-between gap-4">
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" aria-label="Little Joy Baby Shop Home">
                    <img src="{{ $setup->logo_url ?? asset('images/logo.jpeg') }}" height="80" width="200"
                        alt="{{ $setup->shop_name ?? 'Little Joy Baby Shop' }} Logo"
                        class="h-12 md:h-16 w-auto object-contain" />
                </a>
            </div>

            <form action="{{ route('shop.index') }}" method="GET"
                class="hidden lg:block flex-1 max-w-3xl mx-10 relative" id="header-search-container">
                <!-- আপনার অরিজিনাল সার্চ ইনপুট কোড এখানে থাকবে -->
                <div class="relative z-30">
                    <input type="text" name="search" id="header-search-input" autocomplete="off"
                        placeholder="Search by product name"
                        class="w-full py-3 px-6 rounded-full text-gray-700 focus:outline-none bg-white border border-gray-100 shadow-sm" />
                    <button type="submit"
                        class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-600 hover:text-header">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </button>
                </div>

                <!-- Suggestions Dropdown -->
                <div id="search-suggestions"
                    class="hidden absolute top-[90%] left-0 w-full bg-white mt-1 rounded-b-2xl shadow-2xl border border-gray-100 z-60 overflow-hidden pt-4 pb-2">
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
                                    <img src="{{ $p->thumbnail_url }}" height="" width="" alt="product iamge"
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

            <div class="flex items-center gap-3 md:gap-5">
                <button id="mobile-search-btn"
                    class="lg:hidden w-10 h-10 flex items-center justify-center text-[var(--primary-color)] text-xl bg-white rounded-full">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <div class="hidden lg:flex items-center gap-3 md:gap-5">
                    <!-- আপনার অরিজিনাল Wishlist, Cart এবং Account কোড এখানে থাকবে -->
                    <!-- Wishlist -->
                    <a href="{{ route('user.dashboard') }}"
                        class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-full shadow-md flex items-center justify-center hover:shadow-lg hover:scale-105 transition-all duration-200 relative"
                        aria-label="Wishlist">

                        <i class="fa-regular fa-heart text-lg md:text-xl text-[var(--primary-color)]"></i>

                        <span
                            class="wishlist-count-val absolute -top-1 -right-1 min-w-5 h-5 px-1 bg-[var(--primary-color)] text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                            @auth('customer')
                                {{ \App\Models\Wishlist::where('customer_id', auth('customer')->id())->count() }}
                            @else
                                0
                            @endauth
                        </span>
                    </a>


                    <!-- Shopping Cart -->
                    <a href="{{ route('cart.index') }}"
                        class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-full shadow-md flex items-center justify-center hover:shadow-lg hover:scale-105 transition-all duration-200 relative"
                        aria-label="Shopping Cart">

                        <i class="fa-solid fa-bag-shopping text-lg md:text-xl text-[var(--primary-color)]"></i>

                        <span
                            class="cart-count-nav absolute -top-1 -right-1 min-w-5 h-5 px-1 bg-[var(--primary-color)] text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                            {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}
                        </span>
                    </a>


                    <!-- Customer Account -->
                    @auth('customer')

                        <a href="{{ route('user.dashboard') }}"
                            class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-full shadow-md flex items-center justify-center hover:shadow-lg hover:scale-105 transition-all duration-200"
                            aria-label="My Account">

                            <i class="fa-regular fa-circle-user text-lg md:text-xl text-[var(--primary-color)]"></i>

                        </a>

                        <form action="{{ route('user.logout') }}" method="POST" class="inline">
                            @csrf

                            <button type="submit"
                                class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-full shadow-md flex items-center justify-center hover:shadow-lg hover:scale-105 transition-all duration-200 cursor-pointer"
                                aria-label="Logout">

                                <i
                                    class="fa-solid fa-right-from-bracket text-lg md:text-xl text-[var(--primary-color)]"></i>

                            </button>
                        </form>

                    @else

                        <a href="{{ route('user.login') }}"
                            class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-full shadow-md flex items-center justify-center hover:shadow-lg hover:scale-105 transition-all duration-200"
                            aria-label="Login">

                            <i class="fa-regular fa-user text-lg md:text-xl text-[var(--primary-color)]"></i>

                        </a>

                    @endauth
                </div>


            </div>
        </div>

        <div id="mobile-search-expand" class="hidden lg:hidden mt-4 px-2 relative">
            <form action="{{ route('shop.index') }}" method="GET" class="relative z-30" id="mobile-search-container">
                <input type="text" name="search" id="mobile-search-input" placeholder="Search products..."
                    class="w-full py-2.5 px-5 rounded-full bg-white border border-gray-100 shadow-sm text-black" />
                <button type="submit"
                    class="absolute right-1 top-1/2 -translate-y-1/2 w-12 h-10 flex items-center justify-center text-gray-600">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </button>
            </form>

            <!-- মোবাইল সাজেশন ড্রপডাউন (এটি বাদ পড়েছিল) -->
            <div id="mobile-search-suggestions"
                class="hidden absolute top-[100%] left-0 w-full bg-white mt-1 rounded-b-xl shadow-2xl border border-gray-100 z-[110] overflow-hidden pt-4 pb-2">
                <div id="mobile-suggestion-content">
                    <div class="pb-2">
                        <p class="text-[10px] font-bold text-gray-600 uppercase px-5 py-2 tracking-wider">Popular
                            Searches</p>
                        @foreach ($popularSearches as $item)
                            <a href="{{ route('shop.index', ['search' => $item->keyword]) }}"
                                class="block px-5 py-2 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-50 last:border-0">
                                {{ $item->keyword }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <!-- লাইভ সার্চ রেজাল্ট এখানে আসবে -->
                <div id="mobile-live-search-results" class="hidden py-2 border-t border-gray-50"></div>
            </div>
        </div>
    </div>
</header>
<nav class="hidden lg:block shadow-sm bg-white border-b border-gray-100 sticky top-0 z-50">
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
                                                    <a href="{{ $cUrl }}" class="group-hover:text-header uppercase block">
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
<!-- Mobile Bottom Navigation -->
<div
    class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-[100] py-2 shadow-[0_-2px_15px_rgba(0,0,0,0.08)]">
    <!-- grid-cols-4 ব্যবহার করা হয়েছে যাতে ৪টি আইকন সমান গ্যাপ পায় -->
    <div class="grid grid-cols-4 items-center">

        <!-- Home -->
        <a href="{{ route('home') }}"
            class="flex flex-col items-center gap-1 text-gray-500 transition-colors active:text-[var(--primary-color)] {{ request()->routeIs('home') ? 'text-brand' : 'text-gray-500' }}">
            <i class="fa-solid fa-house text-lg"></i>
            <span class="text-[10px] font-bold uppercase">Home</span>
        </a>

        <!-- Cart -->
        <a href="{{ route('cart.index') }}"
            class="flex flex-col items-center gap-1 text-gray-500 relative transition-colors active:text-[var(--primary-color)] {{ request()->routeIs('cart.index') ? 'text-brand' : 'text-gray-500' }}">
            <div class="relative">
                <i class="fa-solid fa-bag-shopping text-lg"></i>

                <!-- এখানে 'cart-count-nav' ক্লাসটি যোগ করা হয়েছে -->
                <span
                    class="cart-count-nav absolute -top-2 -right-2 bg-[var(--primary-color)] text-white text-[9px] rounded-full min-w-[15px] h-[15px] flex items-center justify-center font-bold">
                    {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}
                </span>
            </div>
            <span class="text-[10px] font-bold uppercase">Cart</span>
        </a>

        <!-- Account -->
        <a href="{{ route('user.dashboard') }}"
            class="flex flex-col items-center gap-1 text-gray-500 transition-colors active:text-[var(--primary-color)] {{ request()->routeIs('user.dashboard') ? 'text-brand' : 'text-gray-500' }}"">
            <i class=" fa-regular fa-circle-user text-lg"></i>
            <span class="text-[10px] font-bold uppercase">Account</span>
        </a>

        <!-- Menu Button (Triggers Sidebar) -->
        <button id="bottom-menu-open"
            class="flex flex-col items-center gap-1 text-gray-500 transition-colors active:text-[var(--primary-color)] focus:outline-none ">
            <i class="fa-solid fa-bars-staggered text-lg"></i>
            <span class="text-[10px] font-bold uppercase">Menu</span>
        </button>

    </div>
</div>

<style>
    @media (max-width: 1024px) {
        body {
            padding-bottom: 65px !important;
        }
    }
</style>


<div id="mobile-sidebar"
    class="fixed inset-y-0 left-0 w-80 bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out z-[70] flex flex-col h-screen max-h-screen overflow-hidden">
    <div class="p-4 flex justify-between items-center border-b header-custom-bg text-header">
        <h2 class="font-bold text-lg uppercase tracking-wider">All Categories</h2>
        <button id="close-sidebar" aria-label="Close Menu"
            class="text-2xl hover:text-red-400 transition-colors">&times;</button>
    </div>

    <!-- scrolling-touch ক্লাসটি মোবাইল স্ক্রলকে অনেক স্মুথ করে -->
    <nav class="flex-1 overflow-y-auto pb-40" style="-webkit-overflow-scrolling: touch;">
        @foreach ($headerCategories as $mega)
            <div class="border-b border-gray-100">
                <!-- মেগা ক্যাটাগরি রো -->
                <div class="flex items-center justify-between px-5 py-4 group hover:bg-gray-50">
                    <a href="{{ route('category.products', $mega->slug) }}"
                        class="text-[#0f172a] font-bold uppercase text-sm flex-1">
                        {{ $mega->name }}
                    </a>

                    {{-- যদি সাব-ক্যাটাগরি থাকে তবেই প্লাস আইকন দেখাবে --}}
                    @if ($mega->subCategories->count() > 0)
                        <button class="accordion-btn p-2 text-gray-900 focus:outline-none" data-target="m-cat-{{ $mega->id }}">
                            <i class="fa-solid fa-plus text-sm font-black"></i>
                        </button>
                    @endif
                </div>

                <!-- সাব-ক্যাটাগরি লিস্ট (ডিফল্ট হাইড) -->
                @if ($mega->subCategories->count() > 0)
                    <div id="m-cat-{{ $mega->id }}" class="hidden bg-gray-50 border-t border-gray-100">
                        @foreach ($mega->subCategories as $sub)
                            <div class="border-b border-gray-200 last:border-0">
                                <div class="flex items-center justify-between pl-8 pr-5 py-3">
                                    <a href="{{ route('category.products', $sub->slug) }}"
                                        class="text-sm font-semibold text-gray-700 flex-1">
                                        {{ $sub->name }}
                                    </a>

                                    @if ($sub->miniCategories->count() > 0)
                                        <button class="accordion-btn p-2 text-gray-900" data-target="m-sub-{{ $sub->id }}">
                                            <i class="fa-solid fa-plus text-[10px] font-black"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- মিনি ক্যাটাগরি লিস্ট -->
                                @if ($sub->miniCategories->count() > 0)
                                    <div id="m-sub-{{ $sub->id }}" class="hidden bg-white">
                                        @foreach ($sub->miniCategories as $mini)
                                            <a href="{{ route('category.products', $mini->slug) }}"
                                                class="block pl-12 pr-5 py-2.5 text-xs font-medium text-gray-500 border-b border-gray-50 last:border-0">
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mobileSearchBtn = document.getElementById('mobile-search-btn');
        const mobileSearchExpand = document.getElementById('mobile-search-expand');

        if (mobileSearchBtn) {
            mobileSearchBtn.addEventListener('click', function () {
                mobileSearchExpand.classList.toggle('hidden');
            });
        }
        const bottomMenuBtn = document.getElementById('bottom-menu-open');
        const sidebar = document.getElementById('mobile-sidebar');
        const overlay = document.getElementById('overlay');
        const closeSidebar = document.getElementById('close-sidebar');

        if (bottomMenuBtn) {
            bottomMenuBtn.addEventListener('click', function () {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            });
        }

        // ৩. ক্লোজ বাটন এবং ওভারলে লজিক (আগের আইডি অনুযায়ী)
        if (closeSidebar) {
            closeSidebar.addEventListener('click', function () {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function () {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }
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

            config.input.addEventListener('input', function () {
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
    // ৪. মোবাইল সাইডবার একর্ডিয়ন লজিক (সাব-ক্যাটাগরি দেখানোর জন্য)
    document.querySelectorAll(".accordion-btn").forEach((btn) => {
        btn.addEventListener("click", function (e) {
            e.preventDefault(); // লিঙ্ক হিসেবে কাজ করা আটকাবে
            const targetId = this.getAttribute('data-target');
            const target = document.getElementById(targetId);
            const icon = this.querySelector("i");

            if (target) {
                // সাব-ক্যাটাগরি লিস্ট শো/হাইড করা
                target.classList.toggle("hidden");

                // আইকন প্লাস থেকে মাইনাস করা
                if (icon) {
                    icon.classList.toggle("fa-plus");
                    icon.classList.toggle("fa-minus");
                }
            }
        });
    });
</script>
