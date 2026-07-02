<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $type === 'trial' ? 'Trial Expiry Notice' : 'Subscription Expiry Notice' }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f5f7; font-family: 'Segoe UI', Arial, sans-serif;">

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:30px 0;">
    <tr>
      <td align="center">

        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06);">

          {{-- Header --}}
          <tr>
            <td style="background-color:#4f46e5; padding:28px 40px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="color:#ffffff; font-size:20px; font-weight:600;">
                    {{ config('app.name', 'Dorja.io') }}
                  </td>
                  <td align="right">
                    @if($type === 'trial')
                      <span style="background-color:#facc15; color:#78350f; font-size:12px; font-weight:700; padding:5px 12px; border-radius:20px; text-transform:uppercase;">
                        Trial
                      </span>
                    @else
                      <span style="background-color:#34d399; color:#064e3b; font-size:12px; font-weight:700; padding:5px 12px; border-radius:20px; text-transform:uppercase;">
                        Subscription
                      </span>
                    @endif
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Alert strip --}}
          <tr>
            <td style="background-color:{{ $daysLeft <= 3 ? '#fef2f2' : '#fffbeb' }}; padding:14px 40px; border-bottom:1px solid #f0f0f0;">
              <p style="margin:0; font-size:14px; color:{{ $daysLeft <= 3 ? '#b91c1c' : '#92400e' }}; font-weight:600;">
                @if($daysLeft <= 3)
                  ⚠️ Urgent: Only {{ $daysLeft }} day(s) left!
                @else
                  ⏳ {{ $daysLeft }} day(s) remaining before expiry
                @endif
              </p>
            </td>
          </tr>

          {{-- Body --}}
          <tr>
            <td style="padding:36px 40px;">
              <p style="font-size:16px; color:#111827; margin:0 0 16px 0;">
                Hello <strong>{{ $companyName }}</strong>,
              </p>

              <p style="font-size:15px; color:#374151; line-height:1.6; margin:0 0 20px 0;">
                Your <strong>{{ $type === 'trial' ? 'trial period' : 'subscription' }}</strong>
                will expire on
                <strong style="color:#111827;">{{ \Carbon\Carbon::parse($expiryDate)->format('d M, Y') }}</strong>.
              </p>

              {{-- Info card --}}
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb; border:1px solid #eef0f3; border-radius:8px; margin-bottom:24px;">
                <tr>
                  <td style="padding:18px 20px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="font-size:13px; color:#6b7280; padding-bottom:6px;">Plan Type</td>
                        <td align="right" style="font-size:13px; color:#111827; font-weight:600; padding-bottom:6px;">
                          {{ ucfirst($type) }}
                        </td>
                      </tr>
                      <tr>
                        <td style="font-size:13px; color:#6b7280;">Expiry Date</td>
                        <td align="right" style="font-size:13px; color:#111827; font-weight:600;">
                          {{ \Carbon\Carbon::parse($expiryDate)->format('d M, Y') }}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <p style="font-size:15px; color:#374151; line-height:1.6; margin:0 0 28px 0;">
                Please renew your plan to avoid any service interruption.
              </p>

          
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="background-color:#f9fafb; padding:20px 40px; border-top:1px solid #f0f0f0;">
              <p style="margin:0; font-size:12px; color:#9ca3af; text-align:center;">
                &copy; {{ date('Y') }} {{ config('app.name', 'Dorja.io') }}. All rights reserved.
              </p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>