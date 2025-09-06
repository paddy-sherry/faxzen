<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fax Cover Page</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            line-height: 1.4;
            margin: 0;
            padding: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #8b5cf6;
            padding-bottom: 20px;
        }
        .cover-title {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 8px 20px 8px 0;
            width: 25%;
            border-bottom: 1px solid #eee;
        }
        .info-value {
            display: table-cell;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #8b5cf6;
            margin-bottom: 15px;
            border-bottom: 1px solid #8b5cf6;
            padding-bottom: 5px;
        }
        .message-box {
            border: 1px solid #ddd;
            padding: 20px;
            background-color: #f9f9f9;
            margin-top: 10px;
            min-height: 100px;
        }
        .footer {
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .urgent {
            color: #dc2626;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="cover-title">FAX TRANSMISSION COVER SHEET</h1>
        <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;">Sent via FaxZen.com</p>
    </div>

    <div class="section">
        <div class="section-title">Transmission Details</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Date:</div>
                <div class="info-value">{{ $date }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Time:</div>
                <div class="info-value">{{ $time }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">To Fax Number:</div>
                <div class="info-value">{{ $faxJob->recipient_number }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Pages:</div>
                <div class="info-value">{{ $totalPages }} (including this cover sheet)</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">From</div>
        <div class="info-grid">
            @if($faxJob->cover_sender_name)
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value">{{ $faxJob->cover_sender_name }}</div>
            </div>
            @endif
            @if($faxJob->cover_sender_company)
            <div class="info-row">
                <div class="info-label">Company:</div>
                <div class="info-value">{{ $faxJob->cover_sender_company }}</div>
            </div>
            @endif
            @if($faxJob->cover_sender_phone)
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">{{ $faxJob->cover_sender_phone }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $faxJob->sender_email }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">To</div>
        <div class="info-grid">
            @if($faxJob->cover_recipient_name)
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value">{{ $faxJob->cover_recipient_name }}</div>
            </div>
            @endif
            @if($faxJob->cover_recipient_company)
            <div class="info-row">
                <div class="info-label">Company:</div>
                <div class="info-value">{{ $faxJob->cover_recipient_company }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Fax Number:</div>
                <div class="info-value">{{ $faxJob->recipient_number }}</div>
            </div>
        </div>
    </div>

    @if($faxJob->cover_subject)
    <div class="section">
        <div class="section-title">Subject</div>
        <div style="font-size: 16px; font-weight: bold; padding: 10px 0;">
            {{ $faxJob->cover_subject }}
        </div>
    </div>
    @endif

    @if($faxJob->cover_message)
    <div class="section">
        <div class="section-title">Message</div>
        <div class="message-box">
            {{ $faxJob->cover_message }}
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This fax was sent via FaxZen.com - Professional fax services made simple.</p>
        <p>If you have any questions about this transmission, please contact the sender directly.</p>
    </div>
</body>
</html>
