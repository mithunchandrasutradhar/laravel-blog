<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #374151; margin: 0; padding: 0; background:#f3f4f6; }
        .wrapper { max-width:600px; margin:30px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.1); }
        .header  { background:#1d4ed8; padding:24px 32px; color:#fff; }
        .header h1 { margin:0; font-size:20px; }
        .body    { padding:32px; }
        .field   { margin-bottom:16px; }
        .label   { font-size:12px; text-transform:uppercase; color:#6b7280; letter-spacing:.05em; margin-bottom:4px; }
        .value   { font-size:15px; color:#111827; }
        .message-box { background:#f9fafb; border-left:4px solid #1d4ed8; padding:16px; border-radius:4px; white-space:pre-wrap; font-size:14px; line-height:1.6; }
        .footer  { background:#f9fafb; padding:20px 32px; font-size:12px; color:#9ca3af; border-top:1px solid #e5e7eb; }
        .btn     { display:inline-block; background:#1d4ed8; color:#fff; padding:10px 20px; text-decoration:none; border-radius:6px; font-size:14px; margin-top:20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>📬 New Contact Form Message</h1>
        </div>
        <div class="body">
            <p style="margin-top:0;">You have received a new message via the website contact form.</p>

            <div class="field">
                <div class="label">From</div>
                <div class="value">{{ $contactMessage->name }} &lt;{{ $contactMessage->email }}&gt;</div>
            </div>

            <div class="field">
                <div class="label">Subject</div>
                <div class="value">{{ $contactMessage->subject }}</div>
            </div>

            <div class="field">
                <div class="label">Received</div>
                <div class="value">{{ $contactMessage->created_at->format('F j, Y \a\t g:i A') }}</div>
            </div>

            <div class="field">
                <div class="label">Message</div>
                <div class="message-box">{{ $contactMessage->message }}</div>
            </div>

            <p style="color:#6b7280;font-size:13px;">Reply directly to this email to respond to {{ $contactMessage->name }}.</p>
        </div>
        <div class="footer">
            This notification was sent automatically. IP: {{ $contactMessage->ip_address ?? 'N/A' }}
        </div>
    </div>
</body>
</html>
