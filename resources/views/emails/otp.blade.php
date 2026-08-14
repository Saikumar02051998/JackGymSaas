<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $purpose }} code</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f3f4f6; margin: 0; padding: 24px; }
        .card { max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; }
        .code { font-size: 32px; font-weight: 700; letter-spacing: 8px; text-align: center; padding: 16px; background: #f3f4f6; border-radius: 8px; margin: 24px 0; }
        .muted { color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Your {{ $purpose }} code</h2>
        <p>Use the following code to complete your request:</p>
        <div class="code">{{ $otp }}</div>
        <p>This code is valid for <strong>{{ $expiresInMinutes }} minutes</strong>.</p>
        <p class="muted">If you didn't request this, you can safely ignore this email.</p>
        <p class="muted">Thanks,<br>{{ config('mail.from.name') ?: config('app.name') }}</p>
    </div>
</body>
</html>
