@props(['offer', 'company'])

@php
    $priceData = $offer->display_price_data;
    $salePrice = $priceData->sale_price;
    $regularPrice = $priceData->regular_price;
    $isVar = $priceData->is_variation;

    $avgRating = $offer->reviews_avg_rating ?? 0;
    $totalReviews = $offer->reviews_count ?? 0;
@endphp

<a href="{{ route('product.details', $offer->slug ?? $offer->id) }}" class="group shadow bg-white border border-ash/10 rounded-2xl overflow-hidden hover:border-ember/40 -translate-y-1 flex flex-col h-full">

  <div class="h-52 relative overflow-hidden">
    <img
      src="{{ $offer->thumbnail_url ?? asset('images/template1/frontend/default.webp') }}"
      alt="{{ $offer->title ?? 'Offer' }}"
      class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
    >

    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>

    @if ($regularPrice > $salePrice)
      <span class="absolute top-4 left-4 bg-ember text-white text-[11px] font-mono tracking-wide px-3 py-1 rounded-full">
        @php
          $discountPercent = $regularPrice > 0 ? round((($regularPrice - $salePrice) / $regularPrice) * 100) : 0;
        @endphp
        {{ $discountPercent }}% OFF
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

    @if (($company->is_review ?? 0) == 1)
      <div class="flex items-center gap-1 mt-2.5">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="#C99A45" stroke="#C99A45">
          <polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 17 5.5 21 7.5 13.5 2 9 9 9"/>
        </svg>
        <span class="text-xs font-medium">{{ number_format($avgRating, 1) }}</span>
        <span class="text-xs text-smoke">({{ $totalReviews }})</span>
      </div>
    @endif

    <div class="flex items-center justify-between gap-3 mt-auto pt-4">
      <div class="font-mono shrink-0">
        <span class="text-lg font-semibold text-black">
          @if(($setup->currency_position ?? 'left') == 'left')
            {{ $setup->currency }} {{ number_format($salePrice) }}{{ $isVar ? '+' : '' }}
          @else
            {{ number_format($salePrice) }}{{ $isVar ? '+' : '' }} {{ $setup->currency }}
          @endif
        </span>

        @if ($regularPrice > $salePrice)
          <span class="text-smoke line-through ml-1.5 text-sm">
            @if(($setup->currency_position ?? 'left') == 'left')
              {{ $setup->currency }} {{ number_format($regularPrice) }}
            @else
              {{ number_format($regularPrice) }} {{ $setup->currency }}
            @endif
          </span>
        @endif
      </div>
    </div>

    <span class="block bg-ember group-hover:bg-ember-600 transition-colors text-white text-sm font-medium px-4 py-3 rounded-full w-full mt-3 text-center">
      Order Now
    </span>

  </div>
</a>