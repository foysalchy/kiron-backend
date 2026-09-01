@extends('template5.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.cart-meta', ['setup' => $setup])
@endsection
@section('content')

@if (\Gloudemans\Shoppingcart\Facades\Cart::count() > 0)
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-10 lg:py-20">
  <div class="flex items-center justify-between mb-8">
    <h1 class="font-display font-semibold text-3xl sm:text-4xl text-coal">Your Cart</h1>
    <span class="text-smoke bg-white px-4 py-2 rounded-full border border-coal/10 text-sm">
      {{ \Gloudemans\Shoppingcart\Facades\Cart::count() }} Items
    </span>
  </div>

  <div class="grid lg:grid-cols-3 gap-10">
    <!-- Cart Items -->
    <div class="lg:col-span-2 space-y-6">

      @foreach ($cartContent as $item)
        <div class="bg-white p-5 rounded-2xl border border-coal/10 flex flex-col sm:flex-row gap-5 items-center relative sear-corner">
        <a href="{{ route('cart.remove', $item->rowId) }}"
   class="absolute top-4 right-4 z-20 text-smoke hover:text-red-500 transition-colors pointer-events-auto">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
</a>

          <img src="{{ $item->options->thumbnail }}"
               onerror="this.src='{{ asset('images/template1/frontend/default.webp') }}'"
               alt="{{ $item->name }}"
               class="w-24 h-24 rounded-xl object-cover">

          <div class="flex-1 w-full">
            <a href="{{ route('product.details', $item->options->slug ?? $item->id) }}" class="group/title">
              <h3 class="font-display font-semibold text-lg group-hover/title:text-ember transition-colors">
                {{ $item->name ?? '' }}
              </h3>
            </a>

            @if (!empty($item->options->variant))
              <p class="text-smoke text-sm mt-1">{{ $item->options->variant }}</p>
            @endif

            <div class="flex items-center justify-between mt-4">
              <div>
                <span class="font-mono font-semibold text-ember text-lg">
                  {{ $setup->currency }} {{ number_format($item->price, 0) }}
                </span>
                @if (isset($item->options['regular_price']) && (float) $item->options['regular_price'] > (float) $item->price)
                  <span class="text-smoke line-through text-sm ml-1.5">
                    {{ $setup->currency }} {{ number_format($item->options['regular_price'], 0) }}
                  </span>
                @endif
              </div>

              <div class="flex items-center border border-coal/15 rounded-full bg-ash/50 h-9 w-24">
                <button type="button" onclick="updateCartQty('{{ $item->rowId }}', {{ $item->qty - 1 }})" class="w-8 flex items-center justify-center text-coal hover:text-ember">-</button>
                <span class="flex-1 text-center font-medium text-sm">{{ $item->qty }}</span>
                <button type="button" onclick="updateCartQty('{{ $item->rowId }}', {{ $item->qty + 1 }})" class="w-8 flex items-center justify-center text-coal hover:text-ember">+</button>
              </div>
            </div>

            <div class="flex justify-end mt-2">
              <span class="font-mono font-bold text-coal text-sm">
                Subtotal: {{ $setup->currency }} {{ number_format($item->subtotal, 0) }}
              </span>
            </div>
          </div>
        </div>
      @endforeach

      <div class="pt-6 border-t border-coal/10">
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 text-ember hover:text-ember-700 font-medium transition-colors">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Continue Shopping
        </a>
      </div>
    </div>

    <!-- Order Summary -->
    <div>
      <div class="bg-white p-6 rounded-2xl border border-coal/10 shadow-sm sticky top-28">
        <h3 class="font-display font-semibold text-xl mb-6">Order Summary</h3>

        <!-- Shipping Area -->
        <div class="mb-6">
          <label class="text-sm font-medium text-coal block mb-3">Select Your Shipping Area</label>
          <form action="{{ route('cart.shipping') }}" method="POST" id="shipping-form" class="space-y-2">
            @csrf

            <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors {{ $shipping_area == 'inside' ? 'border-ember bg-ember/5' : 'border-coal/15 hover:border-coal/30' }}">
              <input type="radio" name="area" value="inside" onchange="this.form.submit()"
                     {{ $shipping_area == 'inside' ? 'checked' : '' }}
                     class="accent-ember">
              <span class="text-sm font-medium text-coal">
                Inside Dhaka ({{ $setup->currency }} {{ number_format($setup->inside_charge, 0) }})
              </span>
            </label>

            <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-colors {{ $shipping_area == 'outside' ? 'border-ember bg-ember/5' : 'border-coal/15 hover:border-coal/30' }}">
              <input type="radio" name="area" value="outside" onchange="this.form.submit()"
                     {{ $shipping_area == 'outside' ? 'checked' : '' }}
                     class="accent-ember">
              <span class="text-sm font-medium text-coal">
                Outside Dhaka ({{ $setup->currency }} {{ number_format($setup->outside_charge, 0) }})
              </span>
            </label>
          </form>
        </div>

        <div class="space-y-4 text-sm mb-6">

          <!-- Promo Code -->
          <form action="{{ route('coupon.apply') }}" method="POST">
            @csrf
            <div class="flex gap-2">
              <input type="text" name="coupon_code" placeholder="Promo code"
                     value="{{ session()->has('coupon') ? session('coupon')['coupon_code'] : '' }}"
                     {{ session()->has('coupon') ? 'readonly' : '' }}
                     class="flex-1 border border-coal/15 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-ember">

              @if (session()->has('coupon'))
                <a href="{{ route('coupon.remove') }}" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-600 transition-colors flex items-center">
                  <i class="fas fa-times"></i>
                </a>
              @else
                <button type="submit" class="bg-coal text-white px-4 py-2 rounded-lg text-sm hover:bg-coal-700 transition-colors">Apply</button>
              @endif
            </div>
          </form>

          <div class="pt-4 mt-4 border-t border-coal/10 space-y-4">
            <div class="flex justify-between">
              <span class="text-smoke">Subtotal</span>
              <span class="font-medium">{{ $setup->currency }} {{ number_format($subtotal, 0) }}</span>
            </div>

            @if ($discount > 0)
              <div class="flex justify-between text-green-600">
                <span>Discount ({{ session('coupon')['coupon_code'] }})</span>
                <span>- {{ $setup->currency }} {{ number_format($discount, 0) }}</span>
              </div>
            @endif

            <div class="flex justify-between">
              <span class="text-smoke">Delivery Fee</span>
              <span class="font-medium">{{ $setup->currency }} {{ number_format($shipping, 0) }}</span>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-coal/10 mb-6">
          <div class="flex justify-between items-center">
            <span class="font-semibold text-lg">Total</span>
            <span class="font-mono font-bold text-2xl text-ember">{{ $setup->currency }} {{ number_format($total, 0) }}</span>
          </div>
        </div>

        <a href="{{ url('/checkout') }}" class="w-full bg-ember hover:bg-ember-600 transition-colors text-white h-12 rounded-full flex items-center justify-center gap-2 font-medium">
          Proceed to Checkout
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>

        <p class="text-center text-smoke text-sm font-medium flex items-center justify-center gap-2 mt-4">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
            <path d="M15 18H9"></path>
            <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path>
            <circle cx="17" cy="18" r="2"></circle>
            <circle cx="7" cy="18" r="2"></circle>
          </svg>
          Delivery time in 1-1.3h
        </p>
      </div>
    </div>
  </div>
</section>

<!-- Hidden form for qty updates -->
<form id="update-cart-form" action="{{ route('cart.update') }}" method="POST" style="display: none;">
  @csrf
  <input type="hidden" name="rowId" id="update-row-id">
  <input type="hidden" name="qty" id="update-qty">
</form>

@else
<!-- Empty State -->
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-10 lg:py-20">
  <div class="text-center py-24 bg-white rounded-2xl border border-dashed border-coal/15">
    <div class="w-20 h-20 bg-ash rounded-full flex items-center justify-center mx-auto mb-6">
      <i class="fas fa-shopping-basket text-3xl text-smoke"></i>
    </div>
    <h2 class="font-display font-semibold text-2xl text-coal">Your cart is currently empty!</h2>
    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 mt-8 bg-ember hover:bg-ember-600 transition-colors text-white px-8 py-3 rounded-full font-medium">
      Start Shopping
    </a>
  </div>
</section>
@endif

@endsection

@push('scripts')
<script>
  function updateCartQty(rowId, newQty) {
    if (newQty < 1) return;
    document.getElementById('update-row-id').value = rowId;
    document.getElementById('update-qty').value = newQty;
    document.getElementById('update-cart-form').submit();
  }
</script>
@endpush