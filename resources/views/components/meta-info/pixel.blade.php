@props(['landing' => null, 'setup' => null])

@php
    $landingPage = $landing ?? null;
    $companyId = $landingPage?->company_id ?? ($setup?->company_id ?? null);
    $market = \App\Models\Market::where('company_id', $companyId)->first();
    
    $rawLandingPixel = !empty($landingPage?->pixel) ? trim($landingPage->pixel) : null;
    $fbPixelId = $rawLandingPixel ?: ($market?->facebook_pixel_id ?? null);

    // Facebook Advanced Matching Data
    $customer = auth('customer')->user();
    $fbUserData = [];
    if ($customer) {
        if (!empty($customer->email)) {
            $fbUserData['em'] = hash('sha256', strtolower(trim($customer->email)));
        }
        $phone = preg_replace('/[^0-9]/', '', $customer->phone ?? '');
        if ($phone) {
            if (strlen($phone) == 11 && str_starts_with($phone, '01')) {
                $phone = '88' . $phone;
            }
            $fbUserData['ph'] = hash('sha256', $phone);
        }
        if (!empty($customer->name)) {
            $nameParts = explode(' ', trim($customer->name));
            $fbUserData['fn'] = hash('sha256', strtolower(preg_replace('/[^a-z0-9]/i', '', $nameParts[0])));
            if (count($nameParts) > 1) {
                $fbUserData['ln'] = hash('sha256', strtolower(preg_replace('/[^a-z0-9]/i', '', end($nameParts))));
            }
        }
        if (!empty($customer->district)) {
            $fbUserData['ct'] = hash('sha256', strtolower(preg_replace('/[^a-z0-9]/i', '', $customer->district)));
        }
        if (!empty($customer->division)) {
            $fbUserData['st'] = hash('sha256', strtolower(preg_replace('/[^a-z0-9]/i', '', $customer->division)));
        }
        if (!empty($customer->post_code)) {
            $fbUserData['zp'] = hash('sha256', strtolower(preg_replace('/[^a-z0-9]/i', '', $customer->post_code)));
        }
        $country = strtolower(trim($setup?->country ?? 'bd'));
        $fbUserData['country'] = hash('sha256', $country);
        if (isset($fbUserData['ph'])) {
            $fbUserData['external_id'] = $fbUserData['ph'];
        }
    }
    $fbUserDataJson = !empty($fbUserData) ? json_encode($fbUserData) : '{}';
@endphp

@if(isset($landing) && !empty($landing?->header_code))
    {!! $landing->header_code !!}
@endif

@if($market && !empty($market->domain_verify))
    {!! $market->domain_verify !!}
@endif

@if($market && !empty($market->google_measurement_id))
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $market->google_measurement_id }}');
    </script>
@endif

@if(!empty($fbPixelId))
    @if(str_contains($fbPixelId, '<script'))
        {!! $fbPixelId !!}
    @else
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];}(window, document,'script');
            
            fbq('init', '{{ $fbPixelId }}', {!! $fbUserDataJson !!});
            fbq('track', 'PageView');
            @if(isset($landing) && $landing)
                @php
                    $lProduct = $landing->product ?? ($product ?? null);
                @endphp
                @if($lProduct)
                fbq('track', 'ViewContent', {
                    content_name: '{{ addslashes($lProduct->title ?? $landing->title) }}',
                    content_ids: ['{{ $lProduct->id }}'],
                    content_type: 'product',
                    value: {{ (float) ($landing->discount_price ?? ($landing->regular_price ?? ($lProduct->sale_price ?? 0))) }},
                    currency: 'BDT'
                });
                @endif
            @endif
        </script>
        <noscript>
            <img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id={{ $fbPixelId }}&ev=PageView&noscript=1"/>
        </noscript>
    @endif
@endif

<script>
    let _pixelsLoaded = false;
    function loadTrackingScripts() {
        if(_pixelsLoaded) return;
        _pixelsLoaded = true;

        @if($market && !empty($market->google_measurement_id))
        var g = document.createElement('script');
        g.async = true;
        g.src = 'https://www.googletagmanager.com/gtag/js?id={{ $market->google_measurement_id }}';
        document.head.appendChild(g);
        @endif

        @if(!empty($fbPixelId) && !str_contains($fbPixelId, '<script'))
        var t = document.createElement('script');
        t.async = true;
        t.src = 'https://connect.facebook.net/en_US/fbevents.js';
        var s = document.getElementsByTagName('script')[0];
        if(s && s.parentNode){ s.parentNode.insertBefore(t,s); } else { document.head.appendChild(t); }
        @endif
    }

    // Load scripts as soon as the user interacts with the page (scroll, click, mousemove, touch)
    window.addEventListener('scroll', loadTrackingScripts, {once: true, passive: true});
    window.addEventListener('mousemove', loadTrackingScripts, {once: true, passive: true});
    window.addEventListener('touchstart', loadTrackingScripts, {once: true, passive: true});
    
    // Fallback: If user does nothing, load them automatically after 4 seconds
    setTimeout(loadTrackingScripts, 4000);
</script>
