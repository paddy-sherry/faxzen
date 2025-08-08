<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Account Access</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .title {
            font-size: 24px;
            margin: 15px 0 5px 0;
            font-weight: 600;
        }
        .subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
        }
        .content {
            padding: 40px 30px;
        }
        .access-button {
            display: inline-block;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white !important;
            padding: 16px 32px;
            text-decoration: none !important;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
            transition: all 0.3s ease;
            width: auto;
            max-width: 280px;
        }
        .security-info {
            background-color: #f0f9ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        .footer a {
            color: #8b5cf6;
            text-decoration: none;
        }
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            .container {
                border-radius: 8px;
            }
            .header, .content {
                padding: 20px;
            }
            .access-button {
                display: block !important;
                text-align: center !important;
                margin: 20px auto !important;
                width: 90% !important;
                max-width: 280px !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🔐 FaxZen</div>
            <h1 class="title">Secure Account Access</h1>
            <p class="subtitle">Your secure login link is ready</p>
        </div>

        <div class="content">
            <p style="font-size: 16px; margin-bottom: 20px;">
                Hi there! 👋
            </p>

            <p style="font-size: 16px; margin-bottom: 20px;">
                Someone (hopefully you!) requested access to your FaxZen account for <strong>{{ $email }}</strong>.
            </p>

            <p style="font-size: 16px; margin-bottom: 20px;">
                Click the secure button below to access your account. This link will expire in <strong>1 hour</strong> for your security.
            </p>

            <div style="text-align: center;">
                <a href="{{ $accessUrl }}" class="access-button">
                    🚀 Access My Account Securely
                </a>
            </div>

            <div class="security-info">
                <h4 style="color: #1e40af; margin-top: 0;">🔒 Security Information</h4>
                <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
                    <li>This link expires in 1 hour</li>
                    <li>Can only be used once</li>
                    <li>Only works for {{ $email }}</li>
                    <li>If you didn't request this, you can safely ignore this email</li>
                </ul>
            </div>

            <p style="font-size: 14px; color: #6c757d; margin-top: 30px;">
                <strong>Didn't request this?</strong> No worries! Your account is still secure. 
                This link won't work unless you click it, and it expires automatically.
            </p>

            <p style="font-size: 14px; color: #6c757d;">
                <strong>Need help?</strong> Just reply to this email or visit our 
                <a href="{{ config('app.url') }}/contact" style="color: #8b5cf6;">support page</a>. 
                We're here to help! 💜
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 10px 0;">
                This is a secure access link for your FaxZen account.
            </p>
            <p style="margin: 0;">
                <a href="{{ config('app.url') }}">FaxZen</a> - Professional Fax Services
            </p>
        </div>
    </div>
</body>
</html>