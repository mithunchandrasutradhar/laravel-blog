<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm your subscription</title>
    <style>
        body { margin: 0; padding: 0; background: #f3f4f6; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 40px 40px 32px; text-align: center; }
        .header-logo { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 20px; text-decoration: none; }
        .header-icon { width: 44px; height: 44px; background: rgba(255,255,255,.15); border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; font-weight: 800; }
        .header-brand { font-size: 1.25rem; font-weight: 800; color: #fff; }
        .header h1 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
        .header p  { margin: 8px 0 0; color: rgba(255,255,255,.75); font-size: .9rem; }
        .body { padding: 36px 40px; }
        .body p { margin: 0 0 16px; color: #374151; line-height: 1.7; font-size: .9375rem; }
        .btn-wrap { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; padding: 14px 36px; background: linear-gradient(135deg, #4f46e5, #6366f1); color: #fff !important; text-decoration: none; border-radius: 999px; font-weight: 700; font-size: 1rem; letter-spacing: -.01em; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }
        .fallback { font-size: .8125rem; color: #9ca3af; word-break: break-all; }
        .footer { background: #f9fafb; padding: 20px 40px; text-align: center; }
        .footer p { margin: 0; font-size: .75rem; color: #9ca3af; line-height: 1.6; }
        .footer a { color: #6b7280; }
        .accent { color: #f59e0b; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <div class="header-logo">
            <div class="header-icon">M</div>
            <span class="header-brand">{{ settings('site_name', config('app.name')) }}</span>
        </div>
        <h1>Confirm your subscription</h1>
        <p>One click and you're in <span class="accent">✦</span></p>
    </div>

    {{-- Body --}}
    <div class="body">
        <p>Hi{{ $subscriber->name ? ' ' . $subscriber->name : '' }},</p>

        <p>Thanks for signing up! To complete your subscription and start receiving our newsletter, please confirm your email address by clicking the button below.</p>

        <div class="btn-wrap">
            <a href="{{ route('newsletter.verify', $subscriber->token) }}" class="btn">
                ✓ Confirm my subscription
            </a>
        </div>

        <hr class="divider">

        <p class="fallback">If the button doesn't work, copy and paste this link into your browser:</p>
        <p class="fallback">{{ route('newsletter.verify', $subscriber->token) }}</p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>
            You received this email because someone signed up at
            <a href="{{ config('app.url') }}">{{ settings('site_name', config('app.name')) }}</a>.<br>
            If you didn't request this, you can safely ignore this email.
        </p>
    </div>

</div>
</body>
</html>
