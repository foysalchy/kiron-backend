@php
    // Markets টেবিল থেকে বর্তমান কোম্পানির পিক্সেল আইডি নিয়ে আসা
    $market = \App\Models\Market::where('company_id', $setup->company_id ?? null)->first();
@endphp

@if($market && !empty($market->facebook_pixel_id))
    <!-- Facebook Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $market->facebook_pixel_id }}');
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none"
             src="https://www.facebook.com/tr?id={{ $market->facebook_pixel_id }}&ev=PageView&noscript=1"/>
    </noscript>
    <!-- End Facebook Pixel Code -->

    @if($market->domain_verify)
        {!! $market->domain_verify !!}
    @endif
@endif
