<!-- ============ HEADER (Lumina design) ============ -->
<header class="sticky top-0 z-50 header-custom-bg tick">
  <div class="max-w-7xl mx-auto px-6 lg:px-10">
    <div class="h-24 flex items-center justify-between">

      @php
        // NOTE: swap these route names for whatever your app actually uses —
        // guarded with Route::has() so the header never breaks if a name is missing.
        $homeUrl    = Route::has('home') ? route('home') : url('/');
        $cartUrl    = Route::has('cart.index') ? route('cart.index') : '#';
        $accountUrl = Route::has('account.index') ? route('account.index') : '#';
      @endphp

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
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        </button>

        <a href="{{ $cartUrl }}" class="hidden sm:inline-flex items-center gap-2 primary-bg primary-bg-hover transition-colors text-sm font-medium px-5 py-2.5 rounded-full">
          Order Now
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>

        <button aria-label="Cart" class="relative text-header hover:text-brand transition-colors" onclick="window.location.href='{{ $cartUrl }}'">
          <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3h2l2.4 12.2a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="9" cy="21" r="1"/><circle cx="18" cy="21" r="1"/></svg>
          <span id="cart-count-badge" class="absolute -top-2 -right-2 primary-bg text-[10px] font-mono w-4 h-4 rounded-full flex items-center justify-center">
            {{ $cartCount ?? 0 }}
          </span>
        </button>

        <a href="{{ $accountUrl }}" aria-label="Account" class="hidden sm:flex text-header hover:text-brand transition-colors">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
        </a>
      </div>

    </div>
  </div>
</header>