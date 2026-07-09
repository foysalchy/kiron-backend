{{-- resources/views/emails/reset-password.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Your Password</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e4e4e7; }
    .header { background: #13565e; padding: 32px 40px; text-align: center; }
    .header img { height: 48px; }
    .body { padding: 40px; }
    h1 { margin: 0 0 8px; font-size: 22px; font-weight: 700; color: #111827; }
    p { margin: 0 0 16px; font-size: 15px; line-height: 1.6; color: #374151; }
    .btn { display: inline-block; padding: 14px 32px; background: #13565e; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: 600; margin: 8px 0 24px; }
    .link-block { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; word-break: break-all; font-size: 13px; color: #6b7280; margin-bottom: 24px; }
    .footer { padding: 24px 40px; border-top: 1px solid #f3f4f6; font-size: 13px; color: #9ca3af; text-align: center; }
    .warning { background: #fff7ed; border-left: 4px solid #f97316; padding: 12px 16px; border-radius: 0 8px 8px 0; font-size: 13px; color: #92400e; margin-bottom: 24px; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <img src="{{ asset('favicon.png') }}" alt="{{ config('app.name') }}" />
    </div>

    <div class="body">
      <h1>Reset your password</h1>
      <p>Hi {{ $user->name }},</p>
      <p>We received a request to reset the password for your account. Click the button below to choose a new password. This link expires in <strong>{{ $expiry }} minutes</strong>.</p>

      <div style="text-align: center;">
        <a href="{{ $resetUrl }}" class="btn">Reset My Password</a>
      </div>

      <p style="font-size:13px; color:#6b7280; margin-bottom:8px;">If the button doesn't work, copy and paste this link into your browser:</p>
      <div class="link-block">{{ $resetUrl }}</div>

      <div class="warning">
        <strong>Didn't request this?</strong> You can safely ignore this email. Your password will remain unchanged and this link will expire automatically.
      </div>

      <p>Thanks,<br/><strong>{{ config('app.name') }} Team</strong></p>
    </div>

    <div class="footer">
      &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br/>
      This is an automated message — please do not reply to this email.
    </div>
  </div>
</body>
</html>