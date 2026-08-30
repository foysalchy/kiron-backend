@php
    // Markets টেবিল থেকে বর্তমান কোম্পানির পিক্সেল আইডি নিয়ে আসা
    $market = \App\Models\Market::where('company_id', $setup->company_id ?? null)->first();
    
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
        $country = strtolower(trim($setup->country ?? 'bd'));
        $fbUserData['country'] = hash('sha256', $country);
        if (isset($fbUserData['ph'])) {
            $fbUserData['external_id'] = $fbUserData['ph'];
        }
    }
    $fbUserDataJson = !empty($fbUserData) ? json_encode($fbUserData) : '{}';
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
        fbq('init', '{{ $market->facebook_pixel_id }}', {!! $fbUserDataJson !!});
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
