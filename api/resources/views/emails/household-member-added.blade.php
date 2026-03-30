<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Haushalt</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 36rem; margin: 0 auto; padding: 1.5rem;">
    <h1 style="font-size: 1.25rem;">Du bist einem Haushalt beigetreten</h1>
    <p><strong>{{ $inviter->name }}</strong> hat dich zum Haushalt <strong>{{ $household->name }}</strong> hinzugefügt.</p>
    <p style="margin-top: 1.5rem;">
        <a href="{{ $appUrl }}" style="display: inline-block; background: #16a34a; color: #fff; text-decoration: none; padding: 0.75rem 1.25rem; border-radius: 0.5rem; font-weight: 600;">Zur App</a>
    </p>
    <p style="font-size: 0.875rem; color: #94a3b8;">{{ config('app.name') }}</p>
</body>
</html>
