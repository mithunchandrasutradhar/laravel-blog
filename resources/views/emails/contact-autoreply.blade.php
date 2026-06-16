<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #374151; margin: 0; padding: 0; background:#f3f4f6; }
        .wrapper { max-width:600px; margin:30px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.1); }
        .header  { background:#059669; padding:24px 32px; color:#fff; }
        .header h1 { margin:0; font-size:20px; }
        .body    { padding:32px; line-height:1.7; }
        .quote   { background:#f9fafb; border-left:4px solid #059669; padding:16px; border-radius:4px; margin:20px 0; font-size:14px; white-space:pre-wrap; color:#6b7280; }
        .footer  { background:#f9fafb; padding:20px 32px; font-size:12px; color:#9ca3af; border-top:1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>✅ We received your message!</h1>
        </div>
        <div class="body">
            <p>Hi <strong>{{ $contactMessage->name }}</strong>,</p>
            <p>Thank you for getting in touch. We have received your message and will get back to you as soon as possible — usually within 1–2 business days.</p>

            <p><strong>Your message:</strong></p>
            <div class="quote">{{ $contactMessage->message }}</div>

            <p>If you have any urgent questions, please reply to this email.</p>
            <p>Best regards,<br>The Team</p>
        </div>
        <div class="footer">
            This is an automated confirmation. Please do not reply if your query is already resolved.
        </div>
    </div>
</body>
</html>
