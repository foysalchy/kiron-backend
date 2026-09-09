<!-- ============ HEADER (Lumina design) ============ -->
<header class="sticky top-0 z-50 header-custom-bg tick">
  <div class="max-w-7xl mx-auto px-6 lg:px-10">
    <div class="h-24 flex items-center justify-between">

      @php
      $homeUrl = Route::has('home') ? route('home') : url('/');
      $cartUrl = Route::has('cart.index') ? route('cart.index') : '#';
      $accountUrl = Route::has('account.index') ? route('account.index') : '#';
      @endphp

      <!-- Hamburger (mobile only) -->
      <button type="button" class="lg:hidden text-2xl text-coal focus:outline-none shrink-0" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
        <i class="fas fa-bars"></i>
      </button>
      <a href="{{ $homeUrl }}" class="relative flex items-baseline gap-2 shrink-0 max-w-[45vw] lg:max-w-none group/logo py-2">
        @if($setup->logo_url ?? false)
        <img src="{{ $setup->logo_url }}" alt="{{ $setup->shop_name ?? 'Shop' }}"
          class="w-[100px] max-w-full z-10">
        <span class="absolute -bottom-1 left-0 h-[2.5px] w-full bg-gradient-to-r from-ember via-ember/60 to-transparent scale-x-0 origin-left group-hover/logo:scale-x-100 transition-transform duration-500"></span>
        @else
        <span class="font-semibold text-xl text-header">{{ $setup->shop_name ?? 'Bhaiya Digital' }}</span>
        @endif
      </a>
<nav class="hidden lg:flex items-center gap-10 text-sm bg-[#f8f7f7] py-4 border border-[#e0e0e0] px-4 rounded-full">
  <a href="{{ $homeUrl }}#menu" data-nav-target="menu" class="nav-link text-black hover:text-brand transition-colors">Menu</a>
  <a href="{{ route('reservation.index') }}" class="nav-link text-black hover:text-brand transition-colors {{ request()->routeIs('reservation.index') ? 'active' : '' }}">Reservations</a>
  <a href="{{ $homeUrl }}#offers" data-nav-target="offers" class="nav-link text-black hover:text-brand transition-colors">Offers</a>
</nav>

      <div class="flex items-center gap-5">
        <button id="desktop-search-btn" aria-label="Search" class="hidden sm:flex text-header/80 hover:text-brand transition-colors">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5" />
          </svg>
        </button>


        <button onclick="toggleCartDrawer()" aria-label="Cart" class="relative text-header hover:text-brand transition-colors">
          <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M3 3h2l2.4 12.2a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 2-1.6L21 8H6" />
            <circle cx="9" cy="21" r="1" />
            <circle cx="18" cy="21" r="1" />
          </svg>
          <span id="cart-count-badge" class="cart-count-nav absolute -top-2 -right-2 primary-bg text-[10px] font-mono w-4 h-4 rounded-full flex items-center justify-center">
            {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}
          </span>
        </button>

        <!-- Account (desktop) -->
        <div class="relative cursor-pointer hidden lg:block" id="account-menu">
<div onclick="toggleAccount(event)"
    class="flex items-center gap-1.5 text-gray-700 hover:text-[var(--primary-color)] transition-colors select-none">
    @auth('customer')
    <div class="w-8 h-8 rounded-full overflow-hidden border border-gray-200 flex-shrink-0">
        <img src="{{ auth('customer')->user()->profile_url ?? asset('./images/template1/frontend/user.avif') }}"
            alt="User" class="w-full h-full object-cover">
    </div>
    @else
    <i class="fa-regular fa-user text-xl"></i>
    @endauth
    <span class="hidden lg:block text-[16px]">Account</span>
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
            class="flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-[var(--primary-color)] hover:bg-orange-50 transition-colors">
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
            class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-[var(--primary-color)] hover:bg-orange-50 transition-colors">
            <i class="fa-solid fa-right-to-bracket text-gray-400 text-sm"></i>
            Login
        </a>
        <a href="{{ route('user.register') }}"
            class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-[var(--primary-color)] hover:bg-orange-50 transition-colors">
            <i class="fa-solid fa-user-plus text-gray-400 text-sm"></i>
            Register
        </a>
    </div>
    @endauth
</div>
        </div>

      </div>

    </div>
  </div>

  <!-- ============ SEARCH EXPAND ============ -->
  <div id="header-search-expand" class="hidden border-t border-coal/10 bg-white relative">
    <div class="max-w-3xl mx-auto px-6 py-4 relative">
      <form action="{{ route('shop.index') }}" method="GET" class="relative z-30" id="header-search-container">
        <input type="text" name="search" id="header-search-input" autocomplete="off"
          placeholder="Search by  name"
          class="w-full py-3 px-6 rounded-full text-gray-700 focus:outline-none bg-[#f8f7f7] border border-gray-100" />
        <button type="submit"
          class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-600 hover:text-brand">
          <i class="fa-solid fa-magnifying-glass text-lg"></i>
        </button>
      </form>

      <!-- Suggestions Dropdown -->
      <div id="search-suggestions"
        class="hidden absolute top-[calc(100%-8px)] left-6 right-6 bg-white mt-1 rounded-b-2xl shadow-2xl border border-gray-100 z-20 overflow-hidden pt-4 pb-2">
        <div id="suggestion-content">
          <div class="pb-2">
            <p class="text-[10px] font-bold text-gray-600 uppercase px-5 py-2 tracking-wider">Popular Searches</p>
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
            <p class="text-[10px] font-bold text-gray-600 uppercase px-5 py-2 tracking-wider">Trending Products</p>
            @foreach ($relatedProducts as $p)
            <a href="{{ url($p->slug) }}"
              class="flex items-center gap-3 px-5 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
              <img src="{{ $p->thumbnail_url }}" alt="product image"
                class="w-8 h-8 rounded object-cover border border-gray-100">
              <span class="truncate">{{ $p->title }}</span>
            </a>
            @endforeach
          </div>
        </div>
        <!-- Live Results (typed by user) -->
        <div id="live-search-results" class="hidden py-2 border-t border-gray-50"></div>
      </div>
    </div>
  </div>

  <!-- ============ MOBILE MENU DRAWER ============ -->
  <div id="mobile-menu" class="hidden lg:hidden fixed inset-0 z-[1100] flex">
    <div class="absolute inset-0 bg-black/50" onclick="toggleMobileMenu()"></div>

    <div class="relative w-72 max-w-[85vw] bg-ash h-full overflow-y-auto shadow-2xl flex flex-col">

      <div class="flex items-center justify-between px-5 py-4 border-b border-coal/10 primary-bg text-primary">
        <span class="text-primary font-display font-semibold text-lg">{{ $setup->shop_name ?? 'Menu' }}</span>
        <button type="button" onclick="toggleMobileMenu()" class="text-primary text-xl">
          <i class="fas fa-times"></i>
        </button>
      </div>

      @auth('customer')
      <div class="flex items-center gap-3 px-5 py-4 border-b border-coal/10 bg-white">
        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-ember">
          <img src="{{ auth('customer')->user()->profile_url ?? asset('./images/template1/frontend/user.avif') }}"
            class="w-full h-full object-cover">
        </div>
        <div>
          <p class="text-sm font-bold text-coal">{{ auth('customer')->user()->name }}</p>
          <p class="text-xs text-smoke">{{ auth('customer')->user()->email }}</p>
        </div>
      </div>
      @else
      <div class="flex gap-3 px-5 py-4 border-b border-coal/10 bg-white">
        <a href="{{ route('user.login') }}"
          class="flex-1 text-center py-2 border border-ember text-ember rounded-lg text-sm font-bold">Login</a>
        <a href="{{ route('user.register') }}"
          class="flex-1 text-center py-2 bg-ember text-white rounded-lg text-sm font-bold">Register</a>
      </div>
      @endauth

      <!-- Mobile Search -->
      <div class="px-5 py-3 border-b border-coal/10 bg-white">
        <form action="{{ route('shop.index') }}" method="GET" class="relative" id="mobile-search-container">
          <input type="text" name="search" id="mobile-search-input" autocomplete="off"
            placeholder="Search products..."
            class="w-full py-2.5 px-5 rounded-full bg-[#f8f7f7] border border-gray-100 text-black text-sm" />
          <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 w-10 h-8 flex items-center justify-center text-gray-600">
            <i class="fa-solid fa-magnifying-glass text-sm"></i>
          </button>
        </form>

        <div id="mobile-search-suggestions"
          class="hidden relative bg-white mt-1 rounded-xl shadow-lg border border-gray-100 z-[110] overflow-hidden pt-2 pb-2">
          <div id="mobile-suggestion-content">
            <div class="pb-2">
              <p class="text-[10px] font-bold text-gray-600 uppercase px-4 py-2 tracking-wider">Popular Searches</p>
              @foreach ($popularSearches as $item)
              <a href="{{ route('shop.index', ['search' => $item->keyword]) }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-50 last:border-0">
                {{ $item->keyword }}
              </a>
              @endforeach
            </div>
          </div>
          <div id="mobile-live-search-results" class="hidden py-2 border-t border-gray-50"></div>
        </div>
      </div>

      <nav class="flex-1 px-4 py-3">
        <p class="text-[10px] font-bold text-smoke uppercase px-2 py-2 tracking-wider">Navigation</p>

        <a href="{{ $homeUrl }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-white text-sm font-medium text-coal transition-colors">
          <i class="fas fa-home w-4 text-smoke"></i> Home
        </a>
        <a href="{{ $homeUrl }}#menu" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-white text-sm font-medium text-coal transition-colors">
          <i class="fas fa-utensils w-4 text-smoke"></i> Menu
        </a>
        <a href="{{ route('reservation.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-white text-sm font-medium transition-colors {{ request()->routeIs('reservation.index') ? 'text-brand nav-link active' : 'text-coal' }}">
          <i class="fas fa-calendar-check w-4 {{ request()->routeIs('reservation.index') ? 'text-brand' : 'text-smoke' }}"></i> Reservations
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-white text-sm font-medium text-coal transition-colors">
          <i class="fas fa-star w-4 text-smoke"></i> Experience
        </a>
        <a href="{{ $homeUrl }}#offers" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-white text-sm font-bold text-ember transition-colors">
          <i class="fas fa-bolt w-4 text-ember"></i> Offers
        </a>
        <a href="{{ $cartUrl }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-white text-sm font-medium text-coal transition-colors">
          <i class="fas fa-shopping-cart w-4 text-smoke"></i> Cart
        </a>
      </nav>

      @auth('customer')
      <div class="border-t border-coal/10 p-4 bg-white">
        <a href="{{ route('user.dashboard') }}"
          class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-ash text-sm font-medium text-coal mb-1">
          <i class="fas fa-tachometer-alt w-4 text-smoke"></i> Dashboard
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
  <!-- Cart Drawer Overlay -->
  <div id="cart-overlay" onclick="toggleCartDrawer()"
    class="fixed inset-0 bg-black/50 z-[1100] hidden transition-opacity duration-300"></div>

  <!-- Cart Drawer Panel -->
  <div id="cart-drawer"
    class="fixed top-0 right-0 h-full w-[350px] max-w-[95vw] sm:max-w-[90vw] bg-white z-[1200] shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col">

    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b">
      <div class="text-lg font-bold text-gray-800">Shopping Cart</div>
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
<div class="p-4 border-t bg-gray-50" id="mini-cart-footer"
     style="{{ \Gloudemans\Shoppingcart\Facades\Cart::count() > 0 ? '' : 'display:none;' }}">
  <div class="flex justify-between items-center mb-4">
    <span class="text-lg font-bold text-gray-700">SUBTOTAL:</span>
    <span class="text-lg font-bold text-gray-900" id="mini-cart-subtotal-val"
          data-subtotal-raw="{{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal(0, '', '') }}">
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
</header>

<script>
  document.addEventListener('click', function(e) {
    const link = e.target.closest('a[href*="#"]');
    if (!link) return;

    const href = link.getAttribute('href');
    const hashIndex = href.indexOf('#');
    if (hashIndex === -1) return;

    const hash = href.substring(hashIndex + 1);
    if (!hash) return;

    const targetEl = document.getElementById(hash);
    if (!targetEl) return;

    e.preventDefault();
    e.stopImmediatePropagation();

    // মোবাইল sidebar খোলা থাকলে বন্ধ করে দেওয়া হচ্ছে
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
      toggleMobileMenu();
    }

    const headerHeight = document.querySelector('header')?.offsetHeight || 100;
    const targetPosition = targetEl.getBoundingClientRect().top + window.pageYOffset - (headerHeight + 10);

    // sidebar close animation-এর জন্য সামান্য delay দিয়ে scroll করা হচ্ছে
    setTimeout(() => {
      window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
      });
    }, mobileMenu && !mobileMenu.classList.contains('hidden') ? 50 : 0);

  }, true);
  document.addEventListener('DOMContentLoaded', function () {
  const navLinks = document.querySelectorAll('.nav-link[data-nav-target]');
  if (!navLinks.length) return;

  const sections = [];
  navLinks.forEach(link => {
    const id = link.getAttribute('data-nav-target');
    const el = document.getElementById(id);
    if (el) sections.push({ id, el, link });
  });

  function setActive(id) {
    navLinks.forEach(link => {
      link.classList.toggle('active', link.getAttribute('data-nav-target') === id);
    });
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const match = sections.find(s => s.el === entry.target);
        if (match) setActive(match.id);
      }
    });
  }, {
    root: null,
    rootMargin: '-40% 0px -50% 0px',
    threshold: 0
  });

  sections.forEach(s => observer.observe(s.el));

  navLinks.forEach(link => {
    link.addEventListener('click', function () {
      setActive(this.getAttribute('data-nav-target'));
    });
  });
});
  document.addEventListener('DOMContentLoaded', function() {
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenu) {
      mobileMenu.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
          toggleMobileMenu();
        });
      });
    }
  });

  function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    if (!menu) return;
    menu.classList.toggle('hidden');
    document.body.classList.toggle('overflow-hidden');
  }

  function toggleAccount(e) {
    if (e) e.stopPropagation();
    document.getElementById('account-dropdown').classList.toggle('hidden');
  }

  // dropdown-এর বাইরে ক্লিক করলে বন্ধ হবে
  document.addEventListener('click', function(e) {
    const menu = document.getElementById('account-menu');
    const dropdown = document.getElementById('account-dropdown');
    if (menu && dropdown && !menu.contains(e.target)) {
      dropdown.classList.add('hidden');
    }
  });

  // ============ SEARCH LOGIC ============
  document.addEventListener('DOMContentLoaded', function() {

    // Desktop search toggle
    const desktopSearchBtn = document.getElementById('desktop-search-btn');
    const headerSearchExpand = document.getElementById('header-search-expand');

    if (desktopSearchBtn) {
      desktopSearchBtn.addEventListener('click', function() {
        headerSearchExpand.classList.toggle('hidden');
        const input = document.getElementById('header-search-input');
        if (input && !headerSearchExpand.classList.contains('hidden')) {
          input.focus();
        }
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

      config.input.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(debounceTimer);

        if (query.length > 1) {
          debounceTimer = setTimeout(() => {
            if (config.defaultContent) config.defaultContent.classList.add('hidden');
            config.results.classList.remove('hidden');
            config.results.innerHTML =
              '<div class="px-5 py-3 text-xs text-gray-600"><i class="fas fa-spinner fa-spin mr-2"></i>Searching...</div>';

            fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`)
              .then(res => res.json())
              .then(data => {
                config.results.innerHTML = '';
                if (data.length > 0) {
                  data.forEach(item => {
                    const link = document.createElement('a');
                    link.href = "{{ url('product') }}/" + item.slug;
                    link.className =
                      "flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-50 last:border-0";
                    link.innerHTML = `
                                        <img src="${item.thumbnail_url}" class="w-8 h-8 rounded object-cover border border-gray-100" onerror="this.src='${item.thumbnail_url}'">
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
        if (config.input && !config.input.contains(e.target) && !config.suggestions.contains(e.target)) {
          config.suggestions.classList.add('hidden');
        }
      });
      // desktop search box বন্ধ করা (ইনপুট এবং বাটনের বাইরে ক্লিক করলে)
      if (headerSearchExpand && desktopSearchBtn &&
        !headerSearchExpand.contains(e.target) && !desktopSearchBtn.contains(e.target)) {
        headerSearchExpand.classList.add('hidden');
      }
    });
  });
</script>