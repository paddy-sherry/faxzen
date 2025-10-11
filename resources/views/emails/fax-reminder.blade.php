<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Fax</title>
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
        .highlight-box {
            background-color: #f3f4f6;
            border-left: 4px solid #8b5cf6;
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
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(139, 92, 246, 0.4);
        }
        .benefits {
            background-color: #f0f9ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .benefits h4 {
            color: #0369a1;
            margin-top: 0;
        }
        .benefits ul {
            margin: 0;
            padding-left: 20px;
            color: #075985;
        }
        .benefits li {
            margin: 8px 0;
        }
        .discount-offer {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
        }
        .discount-offer h3 {
            margin: 0 0 10px 0;
            font-size: 22px;
            font-weight: bold;
        }
        .discount-offer .savings {
            font-size: 18px;
            margin: 10px 0;
            opacity: 0.95;
        }
        .discount-offer .original-price {
            text-decoration: line-through;
            opacity: 0.8;
            font-size: 16px;
        }
        .discount-offer .new-price {
            font-size: 24px;
            font-weight: bold;
            color: #fef3c7;
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
        .urgency-indicator {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 12px 20px;
            margin: 0 -30px 20px -30px;
            text-align: center;
            font-weight: 600;
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
                background-color: #8b5cf6 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">📠 FaxZen</div>
            <h1 class="title">Don't forget your fax!</h1>
            <p class="subtitle">Finish sending your fax in a few clicks</p>
        </div>

        <div class="urgency-indicator">
            ⏰ Started {{ $hoursAgo }} hours ago - Complete now to send your fax
        </div>

        <div class="content">
            <p style="font-size: 16px; margin-bottom: 20px;">
                Hi there! 👋
            </p>

            <p style="font-size: 16px; margin-bottom: 20px;">
                We noticed you started sending a fax but didn't finish the process. Your document is ready to go - 
                it just needs your sender information and payment.
            </p>

            <div class="file-info">
                <h4>📄 Your Fax Details</h4>
                <div class="info-row">
                    <span class="info-label">{{ $faxJob->hasMultipleFiles() ? 'Documents:' : 'Document:' }}</span>
                    <span class="info-value">
                        @if($faxJob->hasMultipleFiles())
                            {{ $faxJob->file_count }} files: {{ implode(', ', $faxJob->getAllOriginalNames()) }}
                        @else
                            {{ $fileName }}
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">To:</span>
                    <span class="info-value">{{ $recipientNumber }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Original Cost:</span>
                    <span class="info-value">${{ number_format($faxJob->amount, 2) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Started:</span>
                    <span class="info-value">{{ $faxJob->created_at->format('M j, Y \a\t g:i A') }}</span>
                </div>
            </div>

            <div class="discount-offer">
                <h3>🎉 Special 50% Off Offer!</h3>
                <p class="savings">Complete your fax now and save 50%</p>
                <div>
                    <span class="original-price">${{ number_format($faxJob->amount, 2) }}</span>
                    →
                    <span class="new-price">${{ number_format($faxJob->amount / 2, 2) }}</span>
                </div>
                <p style="font-size: 14px; margin: 15px 0 5px 0; opacity: 0.9;">
                    Limited time offer expires in 24 hours!
                </p>
            </div>

            <div class="highlight-box">
                <strong>🚀 Ready to save and send?</strong> Click below to complete your fax for just ${{ number_format($faxJob->amount / 2, 2) }} instead of ${{ number_format($faxJob->amount, 2) }}!
            </div>

            <div style="text-align: center;">
                <a href="{{ $discountUrl ?? $continueUrl }}" class="cta-button" style="display: inline-block !important; background-color: #dc2626 !important; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white !important; padding: 16px 32px !important; text-decoration: none !important; border-radius: 8px !important; font-weight: 600 !important; font-size: 16px !important; text-align: center !important; margin: 20px auto !important; border: none !important; width: auto !important; max-width: 300px !important; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4) !important;">
                    Get 50% Off - Send for ${{ number_format($faxJob->amount / 2, 2) }} →
                </a>
            </div>

            <div class="benefits">
                <h4>✨ Why customers love FaxZen:</h4>
                <ul>
                    <li><strong>Fast & Reliable:</strong> Documents delivered in seconds</li>
                    <li><strong>Professional Quality:</strong> High-resolution transmission</li>
                    <li><strong>Delivery Confirmation:</strong> Get notified when your fax arrives</li>
                </ul>
            </div>

            <p style="font-size: 14px; color: #6c757d; margin-top: 30px;">
                <strong>Need help?</strong> Just reply to this email or visit our 
                <a href="{{ config('app.url') }}/contact" style="color: #8b5cf6;">support page</a>. 
                We're here to help! 💜
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0 0 10px 0;">
                This is a reminder for your incomplete fax transmission.
            </p>
            <p style="margin: 0;">
                <a href="{{ config('app.url') }}">FaxZen</a> - Professional Fax Services
            </p>
        </div>
    </div>
</body>
</html> 