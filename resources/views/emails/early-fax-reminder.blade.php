<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Reminder - Complete Your Fax</title>
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
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
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
            font-size: 22px;
            margin: 15px 0 5px 0;
            font-weight: 600;
        }
        .subtitle {
            font-size: 15px;
            opacity: 0.9;
            margin: 0;
        }
        .content {
            padding: 40px 30px;
        }
        .gentle-reminder {
            background-color: #ecfdf5;
            border-left: 4px solid #059669;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .file-info {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .file-info h4 {
            margin-top: 0;
            color: #495057;
            font-size: 16px;
            font-weight: 600;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 8px 0;
            flex-wrap: wrap;
            gap: 10px;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 14px;
        }
        .info-value {
            color: #495057;
            font-size: 14px;
            word-break: break-all;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(5, 150, 105, 0.3);
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(5, 150, 105, 0.4);
        }
        .benefits {
            background-color: #fef3c7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .benefits h4 {
            color: #92400e;
            margin-top: 0;
        }
        .benefits ul {
            margin: 0;
            padding-left: 20px;
            color: #a16207;
        }
        .benefits li {
            margin: 8px 0;
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
            color: #059669;
            text-decoration: none;
        }
        .time-indicator {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 12px 20px;
            margin: 0 -30px 20px -30px;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
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
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            .cta-button {
                display: block !important;
                text-align: center !important;
                margin: 20px auto !important;
                width: 90% !important;
                max-width: 280px !important;
                padding: 18px 20px !important;
                font-size: 16px !important;
                background-color: #059669 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">📠 FaxZen</div>
            <h1 class="title">Quick reminder!</h1>
            <p class="subtitle">Your fax is ready to send</p>
        </div>

        <div class="time-indicator">
            📍 Started {{ $hoursAgo }} hours ago - Just 2 minutes to complete
        </div>

        <div class="content">
            <p style="font-size: 16px; margin-bottom: 20px;">
                Hi there! 👋
            </p>

            <p style="font-size: 16px; margin-bottom: 20px;">
                We noticed you uploaded a document but haven't finished sending your fax yet. No worries - 
                it only takes a moment to complete!
            </p>

            <div class="file-info">
                <h4>📄 Your Fax Details</h4>
                <div class="info-row">
                    <span class="info-label">Document:</span>
                    <span class="info-value">{{ $fileName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">To:</span>
                    <span class="info-value">{{ $recipientNumber }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cost:</span>
                    <span class="info-value">${{ number_format($faxJob->amount, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">Ready to send!</span>
                </div>
            </div>

            <div class="gentle-reminder">
                <strong>🚀 Almost there!</strong> Just add your sender details and payment info to send your fax instantly. Takes less than 2 minutes!
            </div>

            <div style="text-align: center;">
                <a href="{{ $continueUrl }}" class="cta-button" style="display: inline-block !important; background-color: #059669 !important; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white !important; padding: 16px 32px !important; text-decoration: none !important; border-radius: 8px !important; font-weight: 600 !important; font-size: 16px !important; text-align: center !important; margin: 20px auto !important; border: none !important; width: auto !important; max-width: 280px !important;">
                    Complete My Fax →
                </a>
            </div>

            <div class="benefits">
                <h4>⭐ Why finish now?</h4>
                <ul>
                    <li><strong>Instant delivery:</strong> Your fax will be sent immediately</li>
                    <li><strong>Delivery confirmation:</strong> You'll know exactly when it arrives</li>
                    <li><strong>Professional quality:</strong> Crystal clear high-resolution faxes</li>
                </ul>
            </div>

            <p style="font-size: 14px; color: #6c757d; margin-top: 30px;">
                <strong>Questions?</strong> Just reply to this email or visit our 
                <a href="{{ config('app.url') }}/contact" style="color: #059669;">support page</a>. 
                We're here to help! 💚
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 10px 0;">
                This is a friendly reminder about your pending fax transmission.
            </p>
            <p style="margin: 0;">
                <a href="{{ config('app.url') }}">FaxZen</a> - Simple, Fast, Reliable Fax Service
            </p>
        </div>
    </div>
</body>
</html> 
