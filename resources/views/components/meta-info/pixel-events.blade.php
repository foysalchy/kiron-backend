 
@props(['event', 'data' => null])

@php
    $market = \App\Models\Market::where('company_id', $setup->company_id ?? null)->first();
    $currencyMap = [
        '$' => 'USD',
        '৳' => 'BDT',
        'tk' => 'BDT',
        'TK' => 'BDT',
        'Tk' => 'BDT',
        'টাকা' => 'BDT',
        '€' => 'EUR',
        '﷼' => 'SAR',
        '(د.إ' => 'AED',
        '£' => 'GBP'
    ];
    $rawCurrency = trim($setup->currency ?? '');
    if (isset($currencyMap[$rawCurrency])) {
        $currencyCode = $currencyMap[$rawCurrency];
    } elseif (preg_match('/^[A-Za-z]{3}$/', $rawCurrency)) {
        $currencyCode = strtoupper($rawCurrency);
    } else {
        $currencyCode = 'BDT';
    }
@endphp

@if($market && !empty($market->facebook_pixel_id))
    <script>
        // product details
        @if($event === 'ViewContent' && isset($data['product']))
            if (typeof fbq !== 'undefined') {
                fbq('track', 'ViewContent', {
                    content_name: '{{ $data['product']->title }}',
                    content_category: '{{ $data['product']->mega_categories->first()->name ?? "General" }}',
                    content_ids: ['{{ $data['product']->id }}'],
                    content_type: 'product',
                    value: {{ $data['product']->sale_price ?? 0 }},
                    currency: '{{ $currencyCode }}'
                });
            }
            @if(!empty($market->tiktok_pixel_id))
            if (typeof ttq !== 'undefined' && typeof ttq.track === 'function') {
                ttq.track('ViewContent', {
                    content_name: '{{ $data['product']->title }}',
                    content_id: '{{ $data['product']->id }}',
                    content_type: 'product',
                    value: {{ $data['product']->sale_price ?? 0 }},
                    currency: '{{ $currencyCode }}'
                });
            }
            @endif
            // checkout page
        @elseif($event === 'InitiateCheckout' && isset($data['total']))
            if (typeof fbq !== 'undefined') {
                fbq('track', 'InitiateCheckout', {
                    value: {{ $data['total'] }},
                    currency: '{{ $currencyCode }}',
                    content_type: 'product'
                });
            }
            @if(!empty($market->tiktok_pixel_id))
            if (typeof ttq !== 'undefined' && typeof ttq.track === 'function') {
                ttq.track('InitiateCheckout', {
                    value: {{ $data['total'] }},
                    currency: '{{ $currencyCode }}',
                    content_type: 'product'
                });
            }
            @endif
            // cart page
        @elseif($event === 'AddToCart' && isset($data['total']))
            if (typeof fbq !== 'undefined') {
                fbq('track', 'AddToCart', {
                    value: {{ $data['total'] }},
                    currency: '{{ $currencyCode }}',
                    content_type: 'product',
                    content_ids: {!! json_encode($data['ids'] ?? []) !!}
                });
            }
            @if(!empty($market->tiktok_pixel_id))
            if (typeof ttq !== 'undefined' && typeof ttq.track === 'function') {
                ttq.track('AddToCart', {
                    value: {{ $data['total'] }},
                    currency: '{{ $currencyCode }}',
                    content_type: 'product',
                    content_id: {!! json_encode($data['ids'] ?? []) !!}
                });
            }
            @endif
            // order details
        @elseif($event === 'Purchase' && isset($data['order']))
            @if(!isset($market->instant_purchase_event) || $market->instant_purchase_event !== false)
            if (typeof fbq !== 'undefined') {
                fbq('track', 'Purchase', {
                    content_ids: ['{{ $data['order']->id }}'],
                    content_type: 'product',
                    value: {{ $data['order']->grand_total ?? $data['order']->total_amount ?? 0 }},
                    currency: '{{ $currencyCode }}'
                }, { eventID: 'ORDER_{{ $data['order']->id }}' });
            }
            @if(!empty($market->tiktok_pixel_id))
            if (typeof ttq !== 'undefined' && typeof ttq.track === 'function') {
                ttq.track('CompletePayment', {
                    content_id: '{{ $data['order']->id }}',
                    content_type: 'product',
                    value: {{ $data['order']->grand_total ?? $data['order']->total_amount ?? 0 }},
                    currency: '{{ $currencyCode }}'
                }, { event_id: 'ORDER_{{ $data['order']->id }}' });
            }
            @endif
            @endif
            // blog details
        @elseif($event === 'ViewBlog' && isset($data['blog']))
            if (typeof fbq !== 'undefined') {
                fbq('track', 'ViewContent', {
                    content_name: '{{ $data['blog']->title }}',
                    content_category: 'Blog',
                    content_type: 'article',
                    content_ids: ['{{ $data['blog']->id }}']
                });
            }
        @endif
    </script>
@endif
