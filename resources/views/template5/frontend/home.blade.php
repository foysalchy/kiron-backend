@extends('template5.layouts.front')
@section('meta')
@include('components.meta-info.ecommerce-meta.index-meta', ['setup' => $setup])
@endsection
@section('content')
<style>
  .thumb-scrollbar::-webkit-scrollbar {
    height: 6px;
  }

  .thumb-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }

  .thumb-scrollbar::-webkit-scrollbar-thumb {
    background-color: var(--primary-color, #D6431F);
    border-radius: 999px;
    opacity: 0.5;
  }

  .thumb-scrollbar::-webkit-scrollbar-thumb:hover {
    opacity: 0.8;
  }

  .thumb-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: var(--primary-color, #D6431F) transparent;
  }

  #menuGrid {
    transition: opacity 0.25s ease;
  }

  #menuGrid.fading {
    opacity: 0;
  }

  .menu-loader {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 3px solid rgba(24, 19, 15, 0.1);
    border-top-color: var(--primary-color, #D6431F);
    animation: menu-spin 0.7s linear infinite;
  }

  @keyframes menu-spin {
    to {
      transform: rotate(360deg);
    }
  }
</style>
<!-- ============ HERO ============ -->
<section class="relative bg-hero overflow-hidden">

  <!-- Slider -->
  <div id="imageSlider" class="relative aspect-[4/3] md:aspect-[16/9] lg:aspect-[21/9] w-full">
    <video
      class="w-full h-full object-cover"
      autoplay
      muted
      loop
      playsinline
      >
      <source src="{{ asset('videos/restaurant.mp4') }}" type="video/mp4">
      Your browser does not support video playback.
    </video>

    <!-- Gradient overlay (Darker on left, transparent on right) -->
    <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent"></div>

    <!-- Hero content - left aligned -->
    <div class="absolute inset-0 flex items-center px-6 md:px-16 lg:px-24">
      <div class="max-w-2xl text-left">
        <!-- Eyebrow -->
        <span class="hero-fade-up hero-delay-1 inline-flex items-center gap-2 text-white/80 tracking-[0.2em] uppercase text-xs md:text-sm mb-4 font-medium">
          <span class="w-8 h-[1px] bg-[var(--primary-color)] block"></span>
          Welcome to {{ $setup->shop_name ?? 'Our Restaurant' }}
        </span>

        <!-- Headline -->
        <h1 class="hero-fade-up hero-delay-2 text-white font-display font-semibold text-4xl md:text-5xl lg:text-7xl leading-[1.1] mb-5 tracking-tight">
          Taste Every Moment,<br> <span class="text-white/90 font-light">Book Your Table.</span>
        </h1>

        <!-- Supporting text -->
        <p class="hero-fade-up hero-delay-3 text-white/80 font-body text-base md:text-lg mb-8 max-w-lg leading-relaxed">
          Fresh flavors, warm ambience, and a seat always waiting for you. Experience dining redefined.
        </p>

        <!-- CTAs -->
        <div class="hero-fade-up hero-delay-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
          <a href="{{ route('reservation.index') }}"
             class="inline-flex items-center justify-center gap-2 bg-[var(--primary-color)] text-white font-medium px-8 py-3.5 rounded-full hover:bg-opacity-90 hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300 w-full sm:w-auto">
            Reserve a Table
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </a>

          <a href="{{ route('shop.index') }}"
             class="inline-flex items-center justify-center gap-2 border border-white/30 text-white font-medium px-8 py-3.5 rounded-full hover:bg-white hover:text-black hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300 w-full sm:w-auto">
            View Menu
          </a>
        </div>
      </div>
    </div>

    <!-- Subtle Scroll Indicator -->
    <div class="absolute bottom-8 left-6 md:left-16 lg:left-24 hero-fade-up hero-delay-5 hidden md:flex items-center gap-3">
      <div class="w-5 h-8 border border-white/30 rounded-full flex justify-center pt-1.5">
        <div class="w-1 h-1.5 bg-white rounded-full animate-bounce"></div>
      </div>
      <span class="text-white/50 text-[10px] font-medium uppercase tracking-[0.2em]">Scroll</span>
    </div>
  </div>

</section>

<style>
  @keyframes heroFadeUp {
    from {
      opacity: 0;
      transform: translateY(16px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .hero-fade-up {
    opacity: 0;
    animation: heroFadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }

  .hero-delay-1 {
    animation-delay: 0.1s;
  }

  .hero-delay-2 {
    animation-delay: 0.25s;
  }

  .hero-delay-3 {
    animation-delay: 0.4s;
  }

  .hero-delay-4 {
    animation-delay: 0.55s;
  }

  .hero-delay-5 {
    animation-delay: 0.7s;
  }

  @media (prefers-reduced-motion: reduce) {
    .hero-fade-up {
      animation: none;
      opacity: 1;
      transform: none;
    }
  }
</style>

<!-- floating info cards -->
<section class="max-w-7xl mx-auto px-6 lg:px-10 -mt-16 lg:-mt-20 relative z-20 mb-2">
  <div class="grid sm:grid-cols-2 gap-5">
    <div class="group sear-corner bg-white border border-coal/10 rounded-2xl p-6 flex items-center gap-5 shadow-[0_20px_50px_-20px_rgba(24,19,15,0.25)] hover:-translate-y-1 transition-transform">
      <div class="w-16 h-16 shrink-0 rounded-xl bg-red-100 flex items-center justify-center">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="1.7">
          <path d="M4 4h16l-1.5 12.5a2 2 0 0 1-2 1.5H7.5a2 2 0 0 1-2-1.5z" />
          <path d="M9 9v6M15 9v6M4 4l1-2h14l1 2" />
        </svg>
      </div>
      <div>
        <h3 class="font-display font-semibold text-lg">Order Online</h3>
        <p class="text-smoke text-sm mt-0.5">Gourmet cuisine delivered straight to your door.</p>
        <a href="#menu" class="inline-flex items-center gap-1.5 text-[var(--primary-color)] font-medium text-sm mt-2">Start Order
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
            <path d="M5 12h14M13 6l6 6-6 6" />
          </svg>
        </a>
      </div>
    </div>

    <div class="group sear-corner bg-white border border-coal/10 rounded-2xl p-6 flex items-center gap-5 shadow-[0_20px_50px_-20px_rgba(24,19,15,0.25)] hover:-translate-y-1 transition-transform">
      <div class="w-16 h-16 shrink-0 rounded-xl bg-red-100 flex items-center justify-center">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="1.7">
          <rect x="3" y="5" width="18" height="16" rx="2" />
          <path d="M8 3v4M16 3v4M3 10h18" />
          <path d="M8 15h2M14 15h2" />
        </svg>
      </div>
      <div>
        <h3 class="font-display font-semibold text-lg">Book a Table</h3>
        <p class="text-smoke text-sm mt-0.5">Reserve your spot for an unforgettable evening.</p>
        <a href="#" class="inline-flex items-center gap-1.5 text-[var(--primary-color)] font-medium text-sm mt-2">Reservations
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
            <path d="M5 12h14M13 6l6 6-6 6" />
          </svg>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============ CATEGORIES (dynamic) ============ -->
@if ($categories->count() > 0)
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-10 reveal">
  <div class="flex items-end justify-between mb-10">
    <div>
      <span class="text-[11px] tracking-[0.22em] uppercase text-brand">What are you looking for?</span>
      <h2 class="font-semibold text-3xl sm:text-4xl mt-2">Browse by category</h2>
    </div>
    @if ($categories->count() > 8)
    <a href="{{ route('categories.all') }}" class="hidden sm:inline-flex items-center gap-1.5 text-sm font-medium hover:text-brand transition-colors">
      View all
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
        <path d="M5 12h14M13 6l6 6-6 6" />
      </svg>
    </a>
    @endif
  </div>

  <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-8 gap-x-4 gap-y-8">
    @foreach ($categories->take(8) as $category)
    <a href="{{ url($category->slug) }}" class="bg-white p-3 rounded shadow-[0_-12px_40px_rgba(214,67,31,0.12)] flex flex-col items-center text-center gap-3 group cursor-pointer">
      <div class="w-full h-17 rounded flex items-center justify-center group-hover:ring-1 group-hover:ring-[var(--primary-color)] group-hover:-translate-y-1 transition-all overflow-hidden">
        <img src="{{ !empty($category->image) ? $category->image_url : asset('images/template1/frontend/default.webp') }}"
          alt="{{ $category->name }}" class="w-full h-full object-cover">
      </div>
      <div>
        <p class="text-sm font-medium">{{ $category->name }}</p>
      </div>
    </a>
    @endforeach
  </div>

  @if ($categories->count() > 8)
  <div class="sm:hidden mt-8 text-center">
    <a href="{{ route('categories.all') }}" class="inline-flex items-center gap-1.5 text-sm font-medium hover:text-brand transition-colors">
      View all
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
        <path d="M5 12h14M13 6l6 6-6 6" />
      </svg>
    </a>
  </div>
  @endif
</section>
@endif
@if ($latestOffers->count() > 0)
<section id="offers" class="relative bg-white text-black grain">
  <div class="absolute inset-0 bg-[url('https://static.vecteezy.com/system/resources/thumbnails/053/329/746/small/fresh-vegetables-isolated-on-white-background-for-healthy-cooking-free-photo.jpeg')] bg-cover bg-bottom bg-no-repeat opacity-10 pointer-events-none"></div>

  <div class="relative max-w-7xl mx-auto px-6 lg:px-10 py-6 md:py-12">

    <div class="flex items-end justify-between mb-10">
      <div>
        <span class="font-mono text-[11px] tracking-[0.22em] uppercase text-[var(--primary-color)]">
          Save more today
        </span>
        <h2 class="font-display font-semibold text-3xl sm:text-4xl mt-2">
          Latest offers
        </h2>
      </div>

      <a href="{{ route('shop.index', ['offers' => 1]) }}" class="hidden sm:inline-flex items-center gap-1.5 text-sm font-medium text-black hover:text-[var(--primary-color)] transition-colors">
        View all offers
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
          <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
      </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
      @foreach ($latestOffers->take(4) as $offer)

      <x-template1.offer-card :offer="$offer" />
      @endforeach
    </div>

    <!-- মোবাইলে View all বাটন গ্রিডের নিচে -->
    <div class="sm:hidden flex justify-center mt-8">
      <a href="{{ route('shop.index', ['offers' => 1]) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-black hover:text-ember transition-colors border border-coal/15 rounded-full px-5 py-2.5">
        View all offers
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
          <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
      </a>
    </div>

  </div>
</section>
@endif

<!-- ============ POPULAR PRODUCTS ("YOU MAY LIKE") ============ -->
@if ($popularProducts->count() > 0)
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-6 md:py-12 reveal" id="menu">
  <div class="flex items-end justify-between mb-2">
    <div>
      <span class="text-[11px] tracking-[0.22em] uppercase text-brand">Something for everyone</span>
      <h2 class="font-semibold text-3xl sm:text-4xl mt-2">Our Menu</h2>
    </div>
  </div>

  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 mt-8 mb-9">

    <div class="flex flex-nowrap gap-2 overflow-x-auto lg:overflow-visible pb-3 lg:pb-0 thumb-scrollbar" id="filterPills">
      <button type="button" data-slug="all"
        class="pill-btn active shrink-0 text-sm font-medium px-4 py-2 rounded-full transition-colors bg-[var(--primary-color)] text-white whitespace-nowrap">
        All
      </button>

      @foreach ($categories->take(8) as $category)
      <button type="button" data-slug="{{ $category->slug }}"
        class="pill-btn shrink-0 text-sm font-medium px-4 py-2 rounded-full transition-colors bg-transparent text-smoke border border-coal/15 hover:border-coal/40 whitespace-nowrap">
        {{ $category->name }}
      </button>
      @endforeach
    </div>

    <div class="flex items-center gap-3 w-full lg:w-auto shrink-0">
      <div class="relative w-full lg:w-auto">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-smoke pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="7" />
          <path d="m20 20-3.5-3.5" />
        </svg>
        <input type="text" id="menuSearchInput" autocomplete="off" placeholder="Search food…"
          class="pl-10 pr-4 py-2.5 rounded-full border border-coal/15 bg-white text-sm w-full lg:w-48 focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)]">
      </div>
    </div>

  </div>

  <div id="menuLoading" class="hidden py-16 flex flex-col items-center justify-center gap-4">
    <div class="menu-loader"></div>
    <span class="text-sm text-smoke tracking-wide">Loading dishes…</span>
  </div>

  <div id="menuGrid" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
    @foreach ($popularProducts as $product)
    <div class="menu-card group sear-corner bg-white border border-[var(--lumina-coal)]/10 rounded-2xl overflow-hidden hover:shadow-[0_24px_50px_-24px_rgba(24,19,15,0.35)] hover:-translate-y-1 transition-all">
      <x-template1.product-card :product="$product" />
    </div>
    @endforeach
  </div>

  <div id="menuNoResults" class="hidden text-center py-16 text-smoke text-sm">
    <i class="fa-solid fa-utensils text-3xl mb-3 text-coal/20 block"></i>
    No items found.
  </div>

  <div class="flex justify-center mt-11">
    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 border border-[var(--primary-color)]/20 hover:border-[var(--primary-color)] transition-colors font-medium px-7 py-3.5 rounded-full">
      View Full Menu
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
        <path d="M5 12h14M13 6l6 6-6 6" />
      </svg>
    </a>
  </div>
</section>


@endif

<!-- ============ FAQ ============ -->
@if ($faqs->count() > 0)
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-6 md:py-12 reveal">
  <div class="bg-white rounded-2xl border border-[var(--lumina-coal)]/10 p-6 md:p-10">
    <h2 class="font-semibold text-2xl md:text-3xl mb-8">Frequently Asked Questions</h2>
    <div class="space-y-8">
      @foreach ($faqs as $faq)
      <div>
        <h3 class="font-semibold text-lg mb-2">{{ $faq->title ?? '' }}</h3>
        <div class="text-[var(--lumina-smoke)] text-sm leading-relaxed">{!! $faq->content !!}</div>
        @if (!$loop->last)
        <hr class="mt-8 border-gray-200">@endif
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function() {

    const slides = document.querySelectorAll(".image-slide");
    const dots = document.querySelectorAll(".slider-dot");
    const nextBtn = document.getElementById("nextImage");
    const prevBtn = document.getElementById("prevImage");

    if (!slides.length) return;

    let current = 0;
    let timer;

    function showSlide(index) {
      if (index >= slides.length) index = 0;
      if (index < 0) index = slides.length - 1;
      current = index;

      slides.forEach((slide, i) => {
        if (i === current) {
          slide.classList.remove("opacity-0", "pointer-events-none");
          slide.classList.add("opacity-100");
        } else {
          slide.classList.remove("opacity-100");
          slide.classList.add("opacity-0", "pointer-events-none");
        }
      });

      dots.forEach((dot, i) => {
        if (i === current) {
          dot.classList.remove("w-2", "bg-white/50");
          dot.classList.add("w-8", "bg-[var(--lumina-ember)]");
        } else {
          dot.classList.remove("w-8", "bg-[var(--lumina-ember)]");
          dot.classList.add("w-2", "bg-white/50");
        }
      });
    }

    function next() {
      showSlide(current + 1);
    }

    function prev() {
      showSlide(current - 1);
    }

    function startAutoSlide() {
      clearInterval(timer);
      timer = setInterval(next, 5000);
    }

    nextBtn?.addEventListener("click", () => {
      next();
      startAutoSlide();
    });
    prevBtn?.addEventListener("click", () => {
      prev();
      startAutoSlide();
    });

    dots.forEach((dot) => {
      dot.addEventListener("click", () => {
        showSlide(Number(dot.dataset.slide));
        startAutoSlide();
      });
    });

    showSlide(0);
    startAutoSlide();
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const pills = document.querySelectorAll('#filterPills .pill-btn');
    const searchInput = document.getElementById('menuSearchInput');
    const grid = document.getElementById('menuGrid');
    const loading = document.getElementById('menuLoading');
    const noResults = document.getElementById('menuNoResults');

    let activeCategory = 'all';
    let debounceTimer;

    function setActivePill(slug) {
      pills.forEach(p => {
        const isActive = p.getAttribute('data-slug') === slug;
        p.classList.toggle('active', isActive);
        p.classList.toggle('bg-[var(--primary-color)]', isActive);
        p.classList.toggle('text-white', isActive);
        p.classList.toggle('bg-transparent', !isActive);
        p.classList.toggle('text-smoke', !isActive);
        p.classList.toggle('border', !isActive);
        p.classList.toggle('border-coal/15', !isActive);
      });
    }

    function loadMenu() {
      noResults.classList.add('hidden');
      grid.classList.add('fading');

      const params = new URLSearchParams({
        category: activeCategory,
        search: searchInput.value.trim(),
      });

      setTimeout(() => {
        loading.classList.remove('hidden');
        grid.classList.add('hidden');

        fetch(`{{ route('menu.filter') }}?${params.toString()}`)
          .then(res => res.text())
          .then(html => {
            loading.classList.add('hidden');
            grid.innerHTML = html;

            if (grid.children.length === 0) {
              noResults.classList.remove('hidden');
            } else {
              grid.classList.remove('hidden');
              requestAnimationFrame(() => {
                grid.classList.remove('fading');
              });
            }
          })
          .catch(() => {
            loading.classList.add('hidden');
            grid.classList.remove('hidden', 'fading');
          });
      }, 200);
    }

    pills.forEach(pill => {
      pill.addEventListener('click', function() {
        activeCategory = this.getAttribute('data-slug');
        setActivePill(activeCategory);
        loadMenu();
      });
    });

    searchInput.addEventListener('input', function() {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(loadMenu, 400);
    });
  });
</script>
@endpush
