<!-- ============ FOOTER (Lumina design) ============ -->
<footer class="footer-custom-bg grain border-t border-gray-200">
  <div class="sear opacity-10"></div>

  <div class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10">

      <!-- Brand -->
      <div>
        <div class="flex items-baseline gap-2">
          <span class="font-semibold text-2xl text-footer">
            {{ $setup->shop_name ?? 'Bhaiya Digital' }}
          </span>
        </div>

        <p class="text-footer/70 text-sm mt-4 leading-relaxed max-w-xs">
          {{ $setup->description ?? 'Quality products, fast delivery, and a shopping experience you can trust.' }}
        </p>

        <div class="flex items-center gap-4 mt-5">
          @if($setup->instagram_url ?? false)
          <a href="{{ $setup->instagram_url }}" aria-label="Instagram" class="text-footer/60 hover:text-brand transition-colors">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
              <rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/>
            </svg>
          </a>
          @endif

          @if($setup->facebook_url ?? false)
          <a href="{{ $setup->facebook_url }}" aria-label="Facebook" class="text-footer/60 hover:text-brand transition-colors">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
              <path d="M14 9h3V5h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V9a1 1 0 0 1 1-1z"/>
            </svg>
          </a>
          @endif

          @if($setup->twitter_url ?? false)
          <a href="{{ $setup->twitter_url }}" aria-label="X" class="text-footer/60 hover:text-brand transition-colors">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
              <path d="m4 4 16 16M20 4 4 20"/>
            </svg>
          </a>
          @endif
        </div>
      </div>

      <!-- Explore -->
      @php
        $homeUrl = Route::has('home') ? route('home') : url('/');
      @endphp
      <div>
        <h4 class="text-[11px] tracking-[0.2em] uppercase text-footer/90 mb-4">Explore</h4>
        <ul class="space-y-2.5 text-sm text-footer/70">
          <li><a href="{{ $homeUrl }}" class="hover:text-brand transition-colors">Home</a></li>
          <li><a href="{{ $homeUrl }}#menu" class="hover:text-brand transition-colors">Menu</a></li>
          <li><a href="#" class="hover:text-brand transition-colors">Reservations</a></li>
          <li><a href="#" class="hover:text-brand transition-colors">Careers</a></li>
        </ul>
      </div>

      <!-- Legal -->
      <div>
        <h4 class="text-[11px] tracking-[0.2em] uppercase text-footer/90 mb-4">Legal</h4>
        <ul class="space-y-2.5 text-sm text-footer/70">
          <li><a href="{{ Route::has('privacy-policy') ? route('privacy-policy') : '#' }}" class="hover:text-brand transition-colors">Privacy Policy</a></li>
          <li><a href="{{ Route::has('terms') ? route('terms') : '#' }}" class="hover:text-brand transition-colors">Terms of Service</a></li>
          <li><a href="{{ Route::has('contact.index') ? route('contact.index') : '#' }}" class="hover:text-brand transition-colors">Contact Us</a></li>
        </ul>
      </div>

      <!-- Newsletter -->
      <div>
        <h4 class="text-[11px] tracking-[0.2em] uppercase text-footer/90 mb-4">Newsletter</h4>
        <p class="text-footer/70 text-sm mb-4">Subscribe to receive exclusive offers and updates.</p>

        <form class="relative" action="{{ Route::has('newsletter.subscribe') ? route('newsletter.subscribe') : '#' }}" method="POST" onsubmit="{{ Route::has('newsletter.subscribe') ? '' : 'return false;' }}">
          @csrf
    <input
            type="email"
            placeholder="Email address"
            class="w-full bg-gray-50 border border-gray-200 rounded-full
                   pl-4 pr-11 py-2.5 text-sm text-black
                   placeholder:text-gray-400
                   focus:outline-none focus:ring-2
                   focus:ring-ember/40 focus:border-ember/50
                   transition-all"
          >
          <button type="submit" aria-label="Subscribe" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full primary-bg primary-bg-hover transition-colors flex items-center justify-center">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
        </form>
      </div>

    </div>

    <!-- Bottom -->
    <div class="border-t border-white/10 mt-12 pt-6 flex flex-col sm:flex-row justify-between gap-3 text-xs text-footer/50">
      <p>&copy; {{ date('Y') }} {{ $setup->shop_name ?? 'Dorja.io' }}. All rights reserved.</p>
      <p>Powered by {{ config('app.name', 'Dorja.io') }}</p>
    </div>
  </div>
</footer>