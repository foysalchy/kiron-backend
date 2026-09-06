@extends('template5.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.product-meta', [
        'setup'         => $setup,
        'megaCategory'  => $megaCategory ?? null,
        'subCategory'   => $subCategory ?? null,
        'miniCategory'  => $miniCategory ?? null,
    ])
@endsection
@section('content')
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-10 reveal">
 
  <div class="mb-10">
    <span class="text-[11px] tracking-[0.22em] uppercase text-brand">What are you looking for?</span>
    <h2 class="font-semibold text-3xl sm:text-4xl mt-2">All Categories</h2>
  </div>
 
  <!-- Search Box -->
  <div class="relative max-w-md mb-10">
    <input type="text" id="category-search-input" autocomplete="off"
      placeholder="Search category..."
      class="w-full py-3 pl-12 pr-5 rounded-full text-gray-700 focus:outline-none bg-[#f8f7f7] border border-gray-100 focus:border-brand transition-colors" />
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
      class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">
      <circle cx="11" cy="11" r="7" />
      <path d="m20 20-3.5-3.5" />
    </svg>
  </div>
 
  <div id="category-grid" class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-8 gap-x-4 gap-y-8">
    @foreach ($categories as $category)
    <a href="{{ url('category/' . $category->slug) }}"
      data-name="{{ strtolower($category->name) }}"
      class="category-card bg-white p-3 rounded shadow-[0_-12px_40px_rgba(214,67,31,0.12)] flex flex-col items-center text-center gap-3 group cursor-pointer">
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
 
  <!-- No results -->
  <div id="category-no-results" class="hidden text-center py-16 text-gray-500">
    <i class="fa-solid fa-magnifying-glass text-3xl mb-3 text-gray-300"></i>
    <p class="text-sm">No category found.</p>
  </div>
 
</section>
 
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('category-search-input');
    const grid = document.getElementById('category-grid');
    const cards = grid ? grid.querySelectorAll('.category-card') : [];
    const noResults = document.getElementById('category-no-results');
 
    if (!searchInput) return;
 
    searchInput.addEventListener('input', function () {
      const query = this.value.trim().toLowerCase();
      let visibleCount = 0;
 
      cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const isMatch = name.includes(query);
        card.classList.toggle('hidden', !isMatch);
        if (isMatch) visibleCount++;
      });
 
      noResults.classList.toggle('hidden', visibleCount !== 0);
      grid.classList.toggle('hidden', visibleCount === 0);
    });
  });
</script>
 

@endsection
