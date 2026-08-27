@props(['offer'])

<a href="{{ route('product.details', $offer->slug ?? $offer->id) }}" class="group shadow bg-white border border-ash/10 rounded-2xl overflow-hidden hover:border-ember/40 -translate-y-1 flex flex-col h-full">

  <div class="h-52 relative overflow-hidden">
    <img
      src="{{ $offer->thumbnail_url ?? asset('images/template1/frontend/default.webp') }}"
      alt="{{ $offer->title ?? 'Offer' }}"
      class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
    >

    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>

    @if (!empty($offer->discount))
      <span class="absolute top-4 left-4 bg-ember text-white text-[11px] font-mono tracking-wide px-3 py-1 rounded-full">
        @if ($offer->discount_type === 'percent')
          {{ (int) $offer->discount }}% OFF
        @else
          {{ $setup->currency }} {{ number_format($offer->discount, 0) }} OFF
        @endif
      </span>
    @endif
  </div>

  <div class="p-6 flex flex-col flex-1">

    <p class="font-display font-semibold text-lg text-gray-800 line-clamp-2 min-h-[28px] group-hover:text-[#BD4F00] transition-colors">
      {{ $offer->title }}
    </p>

    @if (!empty($offer->short_description))
      <p class="hind-siliguri-medium text-sm text-smoke-300 line-clamp-2 mt-1.5 leading-relaxed">
        {{ Str::limit(strip_tags($offer->short_description), 50) }}
      </p>
    @endif

    <div class="flex items-center justify-between gap-3 mt-auto pt-4">
      @if (!empty($offer->price))
        <div class="font-mono shrink-0">
          <span class="text-lg font-semibold text-black">{{ $setup->currency }} {{ number_format($offer->price, 2) }}</span>
          @if (!empty($offer->original_price) && $offer->original_price > $offer->price)
            <span class="text-smoke line-through ml-1.5 text-sm">{{ $setup->currency }} {{ number_format($offer->original_price, 2) }}</span>
          @endif
        </div>
      @endif
    </div>

    <span class="block bg-ember group-hover:bg-ember-600 transition-colors text-white text-sm font-medium px-4 py-3 rounded-full w-full mt-3 text-center">
      Order Now
    </span>

  </div>
</a>