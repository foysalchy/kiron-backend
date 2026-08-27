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

      <a href="{{ $homeUrl }}" class="flex items-baseline gap-2 shrink-0">
        @if($setup->logo_url ?? false)
        <img src="{{ $setup->logo_url }}" alt="{{ $setup->shop_name ?? 'Shop' }}" style="max-height: 100%; width: 100px;">
        @else
        <span class="font-semibold text-xl text-header">{{ $setup->shop_name ?? 'Bhaiya Digital' }}</span>
        @endif
      </a>

      <nav class="hidden lg:flex items-center gap-10 text-sm bg-[#f8f7f7] py-4 border border-[#e0e0e0] px-4 rounded-full">
        <a href="{{ $homeUrl }}#menu" class="text-black hover:text-brand transition-colors">Menu</a>
        <a href="#" class="text-black hover:text-brand transition-colors">Reservations</a>
        <a href="#" class="text-black hover:text-brand transition-colors">Experience</a>
        <a href="{{ $homeUrl }}#offers" class="text-black hover:text-brand transition-colors">Offers</a>
      </nav>

      <div class="flex items-center gap-5">
        <button aria-label="Search" class="hidden sm:flex text-header/80 hover:text-brand transition-colors">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5" />
          </svg>
        </button>

        <a href="{{ $cartUrl }}" class="hidden sm:inline-flex items-center gap-2 primary-bg primary-bg-hover transition-colors text-sm font-medium px-5 py-2.5 rounded-full">
          Order Now
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M5 12h14M13 6l6 6-6 6" />
          </svg>
        </a>

        <button aria-label="Cart" class="relative text-header hover:text-brand transition-colors" onclick="window.location.href='{{ $cartUrl }}'">
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
            class="flex items-center gap-1.5 hover-text transition-colors select-none">
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

      <nav class="flex-1 px-4 py-3">
        <p class="text-[10px] font-bold text-smoke uppercase px-2 py-2 tracking-wider">Navigation</p>

        <a href="{{ $homeUrl }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-white text-sm font-medium text-coal transition-colors">
          <i class="fas fa-home w-4 text-smoke"></i> Home
        </a>
        <a href="{{ $homeUrl }}#menu" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-white text-sm font-medium text-coal transition-colors">
          <i class="fas fa-utensils w-4 text-smoke"></i> Menu
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-white text-sm font-medium text-coal transition-colors">
          <i class="fas fa-calendar-check w-4 text-smoke"></i> Reservations
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

</header>

<script>
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
  document.addEventListener('click', function (e) {
    const menu = document.getElementById('account-menu');
    const dropdown = document.getElementById('account-dropdown');
    if (menu && dropdown && !menu.contains(e.target)) {
      dropdown.classList.add('hidden');
    }
  });
</script>