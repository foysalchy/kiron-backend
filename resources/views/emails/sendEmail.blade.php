<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body style="font-family: sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">

        {{-- Header --}}
        <div style="background-color: #eef1f4; padding: 20px; text-align: center; border-bottom: 3px solid #13565e;">
            @if(!empty($companyLogo))
            <img src="{{ $companyLogo }}" alt="{{ $companyName }}" style="max-height: 40px;">
            @else
            <span style="font-size: 20px; font-weight: bold; color: #13565e;">{{ $companyName }}</span>
            @endif
        </div>

        {{-- Body --}}
        <div style="padding: 20px 25px;">
            {!! $bodyContent !!}
        </div>

        {{-- Footer --}}
        <div style="padding: 20px 25px 30px; border-top: 1px solid #eee;">
            <p style="color: #555; margin: 0 0 16px;">
                If you have any questions, our expert support team is available 24/7 and ready to help.
                <a href="mailto:{{ $supportEmail ?? 'support@' . request()->getHost() }}" style="color: #13565e; text-decoration: none;">Contact us</a> anytime.
            </p>

            <p style="margin: 0 0 16px 0;">
                <strong style="color: #13565e;">{{ $companyName }}</strong> <span style="color: #555;">team</span>
            </p>

            @if(!empty($socials) && count($socials) > 0)
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #eee; text-align: center;">
                <span style="font-size: 14px; color: #777; margin-right: 12px; vertical-align: middle; display: inline-block;">
                    Follow us on:
                </span>
                <span style="display: inline-block; vertical-align: middle;">
                    @foreach($socials as $social)
                    @if(!empty($social['link']) && !empty($social['icon_image']))
                    <a href="{{ $social['link'] }}" target="_blank" style="display: inline-block; margin: 0 6px; text-decoration: none; vertical-align: middle;">
                        <img src="{{ $social['icon_image'] }}" alt="{{ $social['icon_name'] ?? 'Social' }}" width="24" height="24" style="display: block; border: 0; max-width: 24px; max-height: 24px;">
                    </a>
                    @endif  
                    @endforeach
                </span>
            </div>
            @endif
        </div>



    </div>
</body>

</html>