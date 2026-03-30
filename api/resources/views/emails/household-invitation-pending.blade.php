<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Einladung</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 36rem; margin: 0 auto; padding: 1.5rem;">
    <h1 style="font-size: 1.25rem;">Einladung zum Haushalt</h1>
    <p><strong>{{ $inviter->name }}</strong> hat dich eingeladen, dem Haushalt <strong>{{ $household->name }}</strong> beizutreten.</p>
    @if($invite->role === 'sitter')
        <p>Deine Rolle: <strong>Pet-Sitter</strong> (temporärer Zugang).</p>
    @elseif($invite->role === 'admin')
        <p>Deine Rolle: <strong>Administrator</strong>.</p>
    @else
        <p>Deine Rolle: <strong>Mitglied</strong>.</p>
    @endif
    <p style="margin-top: 1.5rem;">
        <a href="{{ $registerUrl }}" style="display: inline-block; background: #16a34a; color: #fff; text-decoration: none; padding: 0.75rem 1.25rem; border-radius: 0.5rem; font-weight: 600;">Konto anlegen &amp; beitreten</a>
    </p>
    <p style="margin-top: 1.25rem; font-size: 0.9375rem; color: #475569;">
        <strong>Schon ein Konto?</strong> Diesen Link öffnen, anmelden — der Beitritt zum Haushalt wird dann automatisch ausgeführt:
    </p>
    <p style="margin-top: 0.5rem;">
        <a href="{{ $acceptUrl }}" style="color: #15803d; font-weight: 600;">Einladung mit bestehendem Konto annehmen</a>
    </p>
    <p style="font-size: 0.875rem; color: #64748b;">Diese E-Mail ging an <strong>{{ $invite->email }}</strong> — nutze dieselbe Adresse bei der Registrierung oder beim Login.</p>
    <p style="font-size: 0.875rem; color: #94a3b8;">{{ config('app.name') }}</p>
</body>
</html>
