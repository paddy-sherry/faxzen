<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fax Delivered Successfully</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #8b5cf6;
            margin-bottom: 10px;
        }
        .success-icon {
            background-color: #10b981;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .title {
            color: #1f2937;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .details-card {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            color: #6b7280;
            font-weight: 500;
        }
        .details-value {
            color: #1f2937;
            font-weight: 600;
        }
        .status-badge {
            background-color: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .cta-button {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
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
                padding: 20px;
            }
            .details-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">📠 FaxZen</div>
            <div class="success-icon"></div>
            <h1 class="title">Fax Delivered Successfully!</h1>
            <p class="subtitle">Your fax has been confirmed as delivered to the recipient. Receipt included below.</p>
        </div>

        <div class="details-card">
            <h3 style="margin-top: 0; color: #1f2937;">Delivery Details</h3>
            
            <div class="details-row">
                <span class="details-label">Document:</span>
                <span class="details-value">{{ $faxJob->file_original_name }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Sent To:</span>
                <span class="details-value">{{ $faxJob->recipient_number }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Delivered:</span>
                <span class="details-value">{{ $deliveredAt }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Status:</span>
                <span class="status-badge">{{ $deliveryStatus }}</span>
            </div>
        </div>

        @if($paymentDetails)
        <!-- Payment Receipt Section -->
        <div class="details-card" style="border-left: 4px solid #8b5cf6;">
            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                <div style="background-color: #8b5cf6; color: white; width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 14px;">💳</div>
                <h3 style="margin: 0; color: #1f2937;">Payment Receipt</h3>
            </div>
            
            <div class="details-row">
                <span class="details-label">Service:</span>
                <span class="details-value">Fax Transmission</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Subtotal:</span>
                <span class="details-value">${{ number_format($paymentDetails['amount_subtotal'], 2) }}</span>
            </div>
            
            @if(isset($paymentDetails['total_details']['amount_tax']) && $paymentDetails['total_details']['amount_tax'] > 0)
            <div class="details-row">
                <span class="details-label">Tax:</span>
                <span class="details-value">${{ number_format($paymentDetails['total_details']['amount_tax'] / 100, 2) }}</span>
            </div>
            @endif
            
            <div class="details-row" style="border-top: 2px solid #e5e7eb; padding-top: 12px; font-weight: 600;">
                <span class="details-label">Total Paid:</span>
                <span class="details-value" style="color: #059669;">${{ number_format($paymentDetails['amount_total'], 2) }} {{ $paymentDetails['currency'] }}</span>
            </div>
            
            @if($paymentDetails['last4'])
            <div class="details-row">
                <span class="details-label">Payment Method:</span>
                <span class="details-value">{{ ucfirst($paymentDetails['brand'] ?? 'Card') }} •••• {{ $paymentDetails['last4'] }}</span>
            </div>
            @endif
            
            <div class="details-row">
                <span class="details-label">Transaction Date:</span>
                <span class="details-value">{{ date('M j, Y \a\t g:i A T', $paymentDetails['created']) }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Transaction ID:</span>
                <span class="details-value" style="font-family: monospace; font-size: 12px;">{{ $paymentDetails['payment_intent_id'] }}</span>
            </div>
            
            @if($paymentDetails['receipt_url'])
            <div style="text-align: center; margin-top: 15px;">
                <a href="{{ $paymentDetails['receipt_url'] }}" style="background-color: #f3f4f6; color: #374151; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">📄 View Official Stripe Receipt</a>
            </div>
            @endif
        </div>
        @else
        <!-- Fallback if payment details not available -->
        <div class="details-card">
            <h3 style="margin-top: 0; color: #1f2937;">Payment Information</h3>
            <div class="details-row">
                <span class="details-label">Amount Paid:</span>
                <span class="details-value">${{ number_format($faxJob->amount, 2) }}</span>
            </div>
        </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <p style="color: #6b7280; margin-bottom: 15px;">Need to send another fax?</p>
            <a href="{{ config('app.url') }}" class="cta-button">Send Another Fax</a>
        </div>

        <div style="background-color: #eff6ff; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h4 style="color: #1e40af; margin-top: 0;">What This Means</h4>
            <ul style="color: #374151; margin: 0; padding-left: 20px;">
                <li>Your document was successfully transmitted to the recipient's fax machine</li>
                <li>The receiving fax machine confirmed receipt of all pages</li>
                <li>Your fax transmission is now complete</li>
            </ul>
        </div>

        <div class="footer">
            <p>Thank you for using <strong>FaxZen</strong> - Making faxing simple, fast, and reliable.</p>
            <p>
                <a href="{{ config('app.url') }}">Visit FaxZen.com</a> | 
                <a href="mailto:support@faxzen.com">Contact Support</a>
            </p>
            @if($paymentDetails && $paymentDetails['receipt_url'])
            <p style="font-size: 13px; color: #6b7280; margin-top: 15px;">
                📧 Keep this email for your records. You can also <a href="{{ $paymentDetails['receipt_url'] }}" style="color: #8b5cf6;">download your official receipt from Stripe</a>.
            </p>
            @endif
            <p style="font-size: 12px; color: #9ca3af; margin-top: 20px;">
                This is an automated message confirming your fax delivery. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html> 