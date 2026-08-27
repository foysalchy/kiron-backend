@props(['offer'])

<div class="group shadow bg-white border border-ash/10 rounded-2xl overflow-hidden hover:border-ember/40 -translate-y-1">

    <div class="h-52 relative overflow-hidden">
        <img
            src="{{ $offer->image_url ?? asset('images/template1/frontend/default.webp') }}"
            alt="{{ $offer->title ?? 'Offer' }}"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">

        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>

        @if (!empty($offer->discount))
        <span class="absolute top-4 left-4 bg-ember text-white text-[11px] font-mono tracking-wide px-3 py-1 rounded-full">
            @if ($offer->discount_type === 'percent')
            {{ rtrim(rtrim(number_format($offer->discount, 2), '0'), '.') }}% OFF
            @else
            {{ $setup->currency }} {{ number_format($offer->discount, 0) }} OFF
            @endif
        </span>
        @endif
    </div>

    <div class="p-6">
        <h3 class="font-display font-semibold text-lg">
            {{ $offer->title }}
        </h3>

        @if (!empty($offer->description))
        <p class="text-smoke-300 text-sm mt-1.5 leading-relaxed">
            {{ $offer->description }}
        </p>
        @endif

        <div class="justify-between mt-2">
            @if (!empty($offer->price))
            <div class="font-mono">
                <span class="text-lg font-semibold text-black">{{ $setup->currency }} {{ number_format($offer->price, 2) }}</span>
                @if (!empty($offer->original_price) && $offer->original_price > $offer->price)
                <span class="text-smoke line-through ml-1.5 text-sm">{{ $setup->currency }} {{ number_format($offer->original_price, 2) }}</span>
                @endif
            </div>
            @endif

            <a href="{{ $offer->url ?? '#' }}" class="block bg-ember hover:bg-ember-600 transition-colors text-white text-sm font-medium px-4 py-3 rounded-full w-full mt-2 text-center">
                Order Now
            </a>
        </div>
    </div>
</div>