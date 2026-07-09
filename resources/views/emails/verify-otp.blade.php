<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verification Code</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f4f5;
            font-family: Arial, sans-serif;
        }

        .wrapper {
            max-width: 520px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .header {
            background: #13565e;
            padding: 28px 40px;
            text-align: center;
        }

        .header h1 {
            color: #fff;
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .body {
            padding: 36px 40px;
        }

        .body p {
            color: #374151;
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 16px;
        }

        .label {
            display: inline-block;
            color: #13565e;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 10px;
            border-radius: 20px;
            border: 1px solid #13565e;
            margin-bottom: 20px;
        }

        .otp-box {
            text-align: center;
            margin: 28px 0;
        }

        .otp {
            display: inline-block;
            letter-spacing: 10px;
            font-size: 40px;
            font-weight: 800;
            color: #13565e;
            background: #f0faf7;
            border: 2px dashed #13565e;
            border-radius: 12px;
            padding: 16px 32px;
            font-family: monospace;
        }

        .expire {
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            margin-top: 8px;
        }

        .note {
            font-size: 13px;
            color: #9ca3af;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
        }

        .footer {
            background: #f9fafb;
            padding: 20px 40px;
            text-align: center;
            border-top: 1px solid #f3f4f6;
        }

        .footer p {
            font-size: 12px;
            color: #9ca3af;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="header">
            <h1>Email Verification Code</h1>
        </div>

        <div class="body">
            <span class="label">
                {{ $type === 'company' ? 'Company Email' : 'Admin User Email' }}
            </span>

            <p>Hello,</p>
            <p>
                Use the verification code below to verify your
                @if($type === 'company')
                <strong>company email address</strong> for <strong>{{ $companyName }}</strong>.
                @else
                <strong>admin account</strong> for <strong>{{ $companyName }}</strong>.
                @endif
            </p>

            <div class="otp-box">
                <div class="otp">{{ $otp }}</div>
                <p class="expire">This code expires in <strong>10 minutes</strong></p>
            </div>

            <p>Enter this code on the verification screen to activate your account.</p>

            <p class="note">
                If you did not register for this account, please ignore this email.
                Do not share this code with anyone.
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>