{{-- resources/views/components/meta-info/ecommerce-meta/pixel-events.blade.php --}}
@props(['event', 'data' => null])

@php
    $market = \App\Models\Market::where('company_id', $setup->company_id ?? null)->first();
@endphp

@if($market && !empty($market->facebook_pixel_id))
    <script>
        // product details
        @if($event === 'ViewContent' && isset($data['product']))
            fbq('track', 'ViewContent', {
                content_name: '{{ $data['product']->title }}',
                content_category: '{{ $data['product']->mega_categories->first()->name ?? "General" }}',
                content_ids: ['{{ $data['product']->id }}'],
                content_type: 'product',
                value: {{ $data['product']->sale_price ?? 0 }},
                currency: '{{ $setup->currency ?? "BDT" }}'
            });
            @if(!empty($market->tiktok_pixel_id))
            ttq.track('ViewContent', {
                content_name: '{{ $data['product']->title }}',
                content_id: '{{ $data['product']->id }}',
                content_type: 'product',
                value: {{ $data['product']->sale_price ?? 0 }},
                currency: '{{ $setup->currency ?? "BDT" }}'
            });
            @endif
            // checkout page
        @elseif($event === 'InitiateCheckout' && isset($data['total']))
            fbq('track', 'InitiateCheckout', {
                value: {{ $data['total'] }},
                currency: '{{ $setup->currency ?? "BDT" }}',
                content_type: 'product'
            });
            @if(!empty($market->tiktok_pixel_id))
            ttq.track('InitiateCheckout', {
                value: {{ $data['total'] }},
                currency: '{{ $setup->currency ?? "BDT" }}',
                content_type: 'product'
            });
            @endif
            // cart page
        @elseif($event === 'AddToCart' && isset($data['total']))
            fbq('track', 'AddToCart', {
                value: {{ $data['total'] }},
                currency: '{{ $setup->currency ?? "BDT" }}',
                content_type: 'product',
                content_ids: {!! json_encode($data['ids'] ?? []) !!}
            });
            @if(!empty($market->tiktok_pixel_id))
            ttq.track('AddToCart', {
                value: {{ $data['total'] }},
                currency: '{{ $setup->currency ?? "BDT" }}',
                content_type: 'product',
                content_id: {!! json_encode($data['ids'] ?? []) !!}
            });
            @endif
            // order details
        @elseif($event === 'Purchase' && isset($data['order']))
            fbq('track', 'Purchase', {
                content_ids: ['{{ $data['order']->id }}'],
                content_type: 'product',
                value: {{ $data['order']->grand_total ?? $data['order']->total_amount }},
                currency: '{{ $setup->currency ?? "BDT" }}'
            }, { eventID: 'ORDER_{{ $data['order']->id }}' });
            @if(!empty($market->tiktok_pixel_id))
            ttq.track('CompletePayment', {
                content_id: '{{ $data['order']->id }}',
                content_type: 'product',
                value: {{ $data['order']->grand_total ?? $data['order']->total_amount }},
                currency: '{{ $setup->currency ?? "BDT" }}'
            }, { event_id: 'ORDER_{{ $data['order']->id }}' });
            @endif
            // blog details
        @elseif($event === 'ViewBlog' && isset($data['blog']))
            fbq('track', 'ViewContent', {
                content_name: '{{ $data['blog']->title }}',
                content_category: 'Blog',
                content_type: 'article',
                content_ids: ['{{ $data['blog']->id }}']
            });
        @endif
    </script>
@endif
