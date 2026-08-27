@extends('template5.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.product-details-meta', ['setup' => $setup])
@endsection
@section('content')
  <!-- ============ FOOD DETAILS ============ -->
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-10 lg:py-20">
  <div class="mb-6 flex items-center gap-2 text-sm text-smoke">
    <a href="home.html" class="hover:text-ember transition-colors">Home</a>
    <span>/</span>
    <a href="home.html#menu" class="hover:text-ember transition-colors">Menu</a>
    <span>/</span>
    <a href="#" class="hover:text-ember transition-colors">Burgers</a>
    <span>/</span>
    <span class="text-coal font-medium">Classic Beef Burger</span>
  </div>

  <div class="grid lg:grid-cols-2 gap-12 lg:gap-20">
    <!-- Image Gallery -->
    <div class="space-y-4">
      <div class="relative h-[400px] lg:h-[500px] w-full rounded-2xl overflow-hidden bg-white shadow-sm sear-corner">
        <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=1200&q=85" alt="Classic Beef Burger" class="w-full h-full object-cover">
        <span class="absolute top-5 left-5 bg-ember text-white text-xs font-mono uppercase tracking-wide px-3 py-1.5 rounded-full">Popular</span>
      </div>
      <div class="grid grid-cols-4 gap-4">
        <button class="h-24 rounded-xl overflow-hidden border-2 border-ember ring-offset-2 ring-2 ring-ember/20">
          <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover">
        </button>
        <button class="h-24 rounded-xl overflow-hidden border border-coal/10 hover:border-ember/50 transition-colors">
          <img src="https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover">
        </button>
        <button class="h-24 rounded-xl overflow-hidden border border-coal/10 hover:border-ember/50 transition-colors">
          <img src="https://images.unsplash.com/photo-1594212691516-436fe26ee57a?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover">
        </button>
      </div>
    </div>

    <!-- Product Info -->
    <div class="flex flex-col justify-center">
      <div class="flex items-center gap-2 mb-3">
        <div class="flex items-center">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="#C99A45" stroke="#C99A45"><polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 17 5.5 21 7.5 13.5 2 9 9 9"/></svg>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="#C99A45" stroke="#C99A45" class="ml-1"><polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 17 5.5 21 7.5 13.5 2 9 9 9"/></svg>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="#C99A45" stroke="#C99A45" class="ml-1"><polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 17 5.5 21 7.5 13.5 2 9 9 9"/></svg>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="#C99A45" stroke="#C99A45" class="ml-1"><polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 17 5.5 21 7.5 13.5 2 9 9 9"/></svg>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C99A45" stroke-width="2" class="ml-1"><polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 17 5.5 21 7.5 13.5 2 9 9 9"/></svg>
        </div>
        <span class="text-sm font-medium">4.8</span>
        <span class="text-sm text-smoke">(320 Reviews)</span>
      </div>

      <h1 class="font-display font-semibold text-4xl sm:text-5xl text-coal leading-tight mb-2">Classic Beef Burger</h1>
      <p class="text-xl font-mono font-semibold text-ember mb-6">$12.99</p>
      
      <p class="text-smoke leading-relaxed mb-8">
        Our signature juicy beef patty, aged cheddar cheese, fresh crisp lettuce, ripe tomatoes, red onions, and our secret house sauce, all sandwiched between a perfectly toasted brioche bun. Served with a side of golden fries.
      </p>

      <div class="mb-8">
        <h3 class="font-display font-semibold mb-3">Select Size</h3>
        <div class="flex gap-3">
          <button class="px-5 py-2.5 rounded-full border border-coal/15 text-sm font-medium hover:border-coal transition-colors">Single</button>
          <button class="px-5 py-2.5 rounded-full border-2 border-ember text-ember text-sm font-medium bg-ember/5">Double (+ $3.00)</button>
        </div>
      </div>

      <div class="mb-8">
        <h3 class="font-display font-semibold mb-3">Add Extras</h3>
        <div class="grid grid-cols-2 gap-3">
          <label class="flex items-center gap-3 p-3 border border-coal/10 rounded-xl cursor-pointer hover:border-ember/50 transition-colors">
            <input type="checkbox" class="w-4 h-4 text-ember rounded border-coal/20 focus:ring-ember">
            <span class="text-sm font-medium flex-1">Extra Cheese</span>
            <span class="text-sm text-smoke">+$1.50</span>
          </label>
          <label class="flex items-center gap-3 p-3 border border-coal/10 rounded-xl cursor-pointer hover:border-ember/50 transition-colors">
            <input type="checkbox" class="w-4 h-4 text-ember rounded border-coal/20 focus:ring-ember">
            <span class="text-sm font-medium flex-1">Bacon</span>
            <span class="text-sm text-smoke">+$2.00</span>
          </label>
          <label class="flex items-center gap-3 p-3 border border-coal/10 rounded-xl cursor-pointer hover:border-ember/50 transition-colors">
            <input type="checkbox" class="w-4 h-4 text-ember rounded border-coal/20 focus:ring-ember">
            <span class="text-sm font-medium flex-1">Jalapeños</span>
            <span class="text-sm text-smoke">+$1.00</span>
          </label>
          <label class="flex items-center gap-3 p-3 border border-coal/10 rounded-xl cursor-pointer hover:border-ember/50 transition-colors">
            <input type="checkbox" class="w-4 h-4 text-ember rounded border-coal/20 focus:ring-ember">
            <span class="text-sm font-medium flex-1">Avocado</span>
            <span class="text-sm text-smoke">+$1.75</span>
          </label>
        </div>
      </div>

      <div class="flex items-center gap-5 mt-auto">
        <div class="flex items-center border border-coal/15 rounded-full bg-white h-12 w-32">
          <button class="w-10 flex items-center justify-center text-coal hover:text-ember transition-colors">-</button>
          <span class="flex-1 text-center font-medium">1</span>
          <button class="w-10 flex items-center justify-center text-coal hover:text-ember transition-colors">+</button>
        </div>
        <a href="cart.html" class="p-2 mb:p-0 flex-1 bg-ember hover:bg-ember-600 transition-colors text-white h-12 rounded-full flex items-center justify-center gap-2 text-xs md:text-lg font-medium">
          Add to Cart - $15.99
        </a>
        <button class="w-12 h-12 rounded-full border border-coal/15 flex items-center justify-center text-coal hover:border-ember hover:text-ember transition-colors">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
      </div>
    </div>
  </div>
</section>

<!-- ============ RELATED PRODUCTS ============ -->
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-10 lg:py-20 border-t border-coal/5">
  <div class="flex items-end justify-between mb-8">
    <h2 class="font-display font-semibold text-2xl sm:text-3xl">You might also like</h2>
    <div class="flex gap-2">
      <button class="w-10 h-10 rounded-full border border-coal/15 flex items-center justify-center hover:bg-coal hover:text-white transition-colors">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      </button>
      <button class="w-10 h-10 rounded-full border border-coal/15 flex items-center justify-center hover:bg-coal hover:text-white transition-colors">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
      </button>
    </div>
  </div>
  
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Card 1 -->
    <article class="menu-card group sear-corner bg-white border border-coal/10 rounded-2xl overflow-hidden hover:shadow-[0_24px_50px_-24px_rgba(24,19,15,0.35)] hover:-translate-y-1 transition-all">
      <div class="h-40 relative overflow-hidden">
        <img src="https://images.unsplash.com/photo-1594212691516-436fe26ee57a?auto=format&fit=crop&w=800&q=85" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
      </div>
      <div class="p-5">
        <h3 class="font-display font-semibold text-base">Crispy Chicken Burger</h3>
        <p class="text-smoke text-xs mt-1">Fried chicken breast with spicy mayo.</p>
        <div class="flex items-center justify-between mt-3">
          <span class="font-mono font-semibold">$11.99</span>
          <button class="w-8 h-8 rounded-full bg-ember/10 hover:bg-ember transition-colors flex items-center justify-center text-ember hover:text-white">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
          </button>
        </div>
      </div>
    </article>
    <!-- Card 2 -->
    <article class="menu-card group sear-corner bg-white border border-coal/10 rounded-2xl overflow-hidden hover:shadow-[0_24px_50px_-24px_rgba(24,19,15,0.35)] hover:-translate-y-1 transition-all">
      <div class="h-40 relative overflow-hidden">
        <img src="https://images.unsplash.com/photo-1607013251379-e6eecfffe234?auto=format&fit=crop&w=800&q=85" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
      </div>
      <div class="p-5">
        <h3 class="font-display font-semibold text-base">Double Cheese Burger</h3>
        <p class="text-smoke text-xs mt-1">Double patty with extra cheese.</p>
        <div class="flex items-center justify-between mt-3">
          <span class="font-mono font-semibold">$15.99</span>
          <button class="w-8 h-8 rounded-full bg-ember/10 hover:bg-ember transition-colors flex items-center justify-center text-ember hover:text-white">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
          </button>
        </div>
      </div>
    </article>
  </div>
</section>
@endsection

@push('scripts')
<script>
  // scroll reveal
  const reveals = document.querySelectorAll('.reveal');
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { threshold: 0.12 });
  reveals.forEach(el => io.observe(el));

  // category active state (visual only)
  document.querySelectorAll('.bg-white p-3 rounded cat-item    shadow-[0_-12px_40px_rgba(214,67,31,0.12)] ').forEach(item => {
    item.addEventListener('click', () => {
      document.querySelectorAll('.bg-white p-3 rounded cat-item    shadow-[0_-12px_40px_rgba(214,67,31,0.12)]  div.rounded-full').forEach(c => c.classList.remove('ring-4','ring-ember/40'));
      item.querySelector('div.rounded-full').classList.add('ring-4','ring-ember/40');
    });
  });

  // menu filter pills
  const pills = document.querySelectorAll('.pill-btn');
  const cards = document.querySelectorAll('.menu-card');
  pills.forEach(btn => {
    btn.addEventListener('click', () => {
      pills.forEach(p => p.className = 'pill-btn text-sm font-medium px-4 py-2 rounded-full bg-transparent text-smoke border border-coal/15 hover:border-coal/40 transition-colors');
      btn.className = 'pill-btn text-sm font-medium px-4 py-2 rounded  text-black transition-colors';
      const f = btn.dataset.filter;
      cards.forEach(card => {
        const cats = card.dataset.cats.split(' ');
        const show = f === 'all' || cats.includes(f);
        card.style.display = show ? '' : 'none';
      });
    });
  });
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

  const slides = document.querySelectorAll(".image-slide");
  const dots = document.querySelectorAll(".slider-dot");

  const nextBtn = document.getElementById("nextImage");
  const prevBtn = document.getElementById("prevImage");

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
        dot.classList.add("w-8", "bg-ember");
      } else {
        dot.classList.remove("w-8", "bg-ember");
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

    timer = setInterval(() => {
      next();
    }, 5000);

  }


  nextBtn.addEventListener("click", () => {
    next();
    startAutoSlide();
  });


  prevBtn.addEventListener("click", () => {
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
  document.addEventListener("DOMContentLoaded", function () {

    const lenis = new Lenis({
      duration: 1.2,
      smoothWheel: true,
      smoothTouch: false,
      wheelMultiplier: 1,
      touchMultiplier: 1.5,
      easing: (t) => 1 - Math.pow(1 - t, 4)
    });

    function raf(time) {
      lenis.raf(time);
      requestAnimationFrame(raf);
    }

    requestAnimationFrame(raf);


    // Smooth anchor scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {

      anchor.addEventListener("click", function (e) {

        const target = document.querySelector(this.getAttribute("href"));

        if (!target) return;

        e.preventDefault();

        lenis.scrollTo(target, {
          offset: 0,
          duration: 1.2
        });

      });

    });

  });
</script>
@endpush
@push('scripts')
    @include('components.meta-info.pixel-events', ['event' => 'ViewContent', 'data' => ['product' => $product]])
@endpush

