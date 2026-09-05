<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: 'Segoe UI', Arial, sans-serif; color: #333;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 30px 0;">
        <tr>
            <td align="center">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1a1a2e; padding: 25px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; letter-spacing: 1px;">
                                dorja<span style="color: #4f9dff;">.io</span>
                            </h1>
                        </td>
                    </tr>

                    <!-- Banner -->
                    <tr>
                        <td style="background-color: #4f9dff; padding: 12px; text-align: center;">
                            <p style="margin: 0; color: #ffffff; font-size: 14px; font-weight: bold;">
                                🔑 Password Reset Code
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 35px 30px; text-align: center;">
                            <p style="font-size: 16px; margin: 0 0 15px; text-align: left;">
                                Dear <strong>{{ $name }}</strong>,
                            </p>

                            <p style="font-size: 15px; margin: 0 0 25px; color: #555; text-align: left;">
                                Use the code below to reset your password. This code is valid for the next 10 minutes.
                            </p>

                            <div style="text-align: center; margin: 25px 0;">
                                <span style="display: inline-block; font-size: 32px; font-weight: bold; letter-spacing: 8px; background: #f0f4ff; color: #1a1a2e; padding: 16px 28px; border-radius: 6px; border: 1px dashed #4f9dff;">
                                    {{ $otp }}
                                </span>
                            </div>

                            <div style="background-color: #fff8e6; border-left: 4px solid #ffb020; padding: 14px 18px; border-radius: 4px; margin: 25px 0; text-align: left;">
                                <p style="margin: 0; font-size: 14px; color: #7a5c00;">
                                    ⚠️ If you didn't request this, you can safely ignore this email.
                                </p>
                            </div>

                            <p style="font-size: 13px; color: #999; margin-top: 25px; text-align: left;">
                                If you have any questions, our expert support team is available 24/7 and ready to help.
                                <a href="mailto:{{ config('mail.from.address') }}" style="color: #4f9dff; text-decoration: none;">Contact us</a> anytime.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f0f0f5; padding: 20px 30px; text-align: center;">
                            <p style="margin: 0 0 8px; font-size: 13px; color: #888;">
                                &copy; {{ date('Y') }} dorja.io — All rights reserved.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #aaa;">
                                This is an automated email, please do not reply directly.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>