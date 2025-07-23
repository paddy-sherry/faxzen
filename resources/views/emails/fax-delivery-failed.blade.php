<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fax Delivery Failed</title>
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
        .error-icon {
            background-color: #ef4444;
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
        .error-badge {
            background-color: #ef4444;
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
            margin: 10px 5px;
        }
        .support-button {
            background: #6b7280;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin: 10px 5px;
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
        .refund-notice {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
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
            .cta-button, .support-button {
                display: block;
                text-align: center;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">📠 FaxZen</div>
            <div class="error-icon">✕</div>
            <h1 class="title">Fax Delivery Failed</h1>
            <p class="subtitle">We were unable to deliver your fax to the recipient.</p>
        </div>

        <div class="details-card">
            <h3 style="margin-top: 0; color: #1f2937;">Delivery Attempt Details</h3>
            
            <div class="details-row">
                <span class="details-label">Document:</span>
                <span class="details-value">{{ $faxJob->file_original_name }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Attempted To:</span>
                <span class="details-value">{{ $faxJob->recipient_number }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Failed At:</span>
                <span class="details-value">{{ $failedAt }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Status:</span>
                <span class="error-badge">Failed</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Failure Reason:</span>
                <span class="details-value">{{ $failureReason }}</span>
            </div>
        </div>

        <div class="refund-notice">
            <h4 style="color: #92400e; margin-top: 0;">💰 Automatic Refund</h4>
            <p style="color: #92400e; margin-bottom: 0;">
                Don't worry - you will receive a full refund of ${{ number_format($faxJob->amount, 2) }} within 3-5 business days. 
                No action is required on your part.
            </p>
        </div>

        <div style="background-color: #fef2f2; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h4 style="color: #dc2626; margin-top: 0;">
                @if(str_contains(strtolower($failureReason), 'receiver_call_dropped'))
                    What Happened: Receiver Disconnected
                @elseif(str_contains(strtolower($failureReason), 'sender_call_dropped'))
                    What Happened: Sender Disconnected
                @else
                    Common Reasons for Failure
                @endif
            </h4>
            
            @if(str_contains(strtolower($failureReason), 'ecm') || str_contains(strtolower($failureReason), 'error_correction'))
                <p style="color: #374151; margin: 0 0 10px 0;">
                    <strong>ECM Compatibility Issue:</strong> The receiving fax machine has Error Correction Mode (ECM) compatibility problems.
                </p>
                <ul style="color: #374151; margin: 0; padding-left: 20px;">
                    <li>The recipient's fax machine uses incompatible ECM settings</li>
                    <li>Their fax machine may be older and have ECM bugs</li>
                    <li>Network quality issues are interfering with ECM protocol</li>
                </ul>
                <p style="color: #d97706; margin: 10px 0 0 0; font-weight: 600;">
                    🔧 <strong>Solution:</strong> Ask the recipient to temporarily disable ECM (Error Correction Mode) on their fax machine, then try sending again.
                </p>
            @elseif(str_contains(strtolower($failureReason), 'receiver_call_dropped'))
                <p style="color: #374151; margin: 0 0 10px 0;">
                    The receiving fax machine disconnected during transmission. This commonly happens when:
                </p>
                <ul style="color: #374151; margin: 0; padding-left: 20px;">
                    <li>The recipient's fax machine ran out of paper during transmission</li>
                    <li>Power issues or network problems at the receiving location</li>
                    <li>The receiving fax machine has compatibility issues</li>
                    <li>Line interference caused the connection to drop</li>
                </ul>
                <p style="color: #059669; margin: 10px 0 0 0; font-weight: 600;">
                    💡 <strong>Tip:</strong> This error often resolves on retry. Contact the recipient to ensure their fax machine has paper and is ready.
                </p>
            @elseif(str_contains(strtolower($failureReason), 'sender_call_dropped'))
                <p style="color: #374151; margin: 0 0 10px 0;">
                    The connection was dropped during transmission. This may have been caused by network issues.
                </p>
                <p style="color: #059669; margin: 10px 0 0 0; font-weight: 600;">
                    💡 <strong>Tip:</strong> This error often resolves on retry.
                </p>
            @else
                <ul style="color: #374151; margin: 0; padding-left: 20px;">
                    <li>The recipient's fax machine was busy or not answering</li>
                    <li>The fax number may be incorrect or out of service</li>
                    <li>The recipient's fax machine ran out of paper or toner</li>
                    <li>Network connectivity issues during transmission</li>
                </ul>
            @endif
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <p style="color: #6b7280; margin-bottom: 15px;">What would you like to do next?</p>
            <a href="{{ config('app.url') }}" class="cta-button">Try Again</a>
            <a href="mailto:support@faxzen.com?subject=Fax Delivery Failed - {{ $faxJob->file_original_name }}" class="support-button">Contact Support</a>
        </div>

        <div style="background-color: #eff6ff; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h4 style="color: #1e40af; margin-top: 0;">Need Help?</h4>
            <p style="color: #374151; margin-bottom: 10px;">Our support team is here to help:</p>
            <ul style="color: #374151; margin: 0; padding-left: 20px;">
                <li>Verify the recipient's fax number is correct</li>
                <li>Try sending during business hours when fax machines are more likely to be available</li>
                <li>Contact us if you continue to experience issues</li>
            </ul>
        </div>

        <div class="footer">
            <p>We apologize for the inconvenience. Thank you for using <strong>FaxZen</strong>.</p>
            <p>
                <a href="{{ config('app.url') }}">Visit FaxZen.com</a> | 
                <a href="mailto:support@faxzen.com">Contact Support</a>
            </p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 20px;">
                This is an automated message regarding your fax delivery attempt. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html> 