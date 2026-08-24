<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Password Changed</title>
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
                                🔒 Password Changed Successfully
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 35px 30px;">
                            <p style="font-size: 16px; margin: 0 0 15px;">
                                Dear <strong>{{ $user->name }}</strong>,
                            </p>

                            <p style="font-size: 15px; margin: 0 0 20px; color: #555;">
                                Your account password has been changed. Your new password is shown below:
                            </p>

                            <div style="text-align: center; margin: 25px 0;">
                                <span style="display: inline-block; font-size: 20px; font-weight: bold; letter-spacing: 1px; background: #f0f4ff; color: #1a1a2e; padding: 14px 28px; border-radius: 6px; border: 1px dashed #4f9dff;">
                                    {{ $newPassword }}
                                </span>
                            </div>

                            <div style="background-color: #fff8e6; border-left: 4px solid #ffb020; padding: 14px 18px; border-radius: 4px; margin: 25px 0;">
                                <p style="margin: 0; font-size: 14px; color: #7a5c00;">
                                    ⚠️ For your security, please log in and change this password immediately.
                                </p>
                            </div>

                            <div style="text-align: center; margin: 30px 0 10px;">
                                <a href="https://app.dorja.io/login" style="display: inline-block; background-color: #1a1a2e; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: bold; padding: 12px 32px; border-radius: 6px;">
                                    Login &amp; Change Password
                                </a>
                            </div>

                            <p style="font-size: 13px; color: #999; margin-top: 25px;">
                                If you did not request this change, please contact our support team immediately.
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