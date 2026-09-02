@foreach ($products as $product)
<div class="menu-card group sear-corner bg-white border border-[var(--lumina-coal)]/10 rounded-2xl overflow-hidden hover:shadow-[0_24px_50px_-24px_rgba(24,19,15,0.35)] hover:-translate-y-1 transition-all">
  <x-template1.product-card :product="$product" />
</div>
@endforeach

