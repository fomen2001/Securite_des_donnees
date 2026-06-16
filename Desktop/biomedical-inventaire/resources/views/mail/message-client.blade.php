<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 14px; color: #1a1a2e; background: #f0f4f8; margin: 0; padding: 0; }
    .wrap { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #0D47A1 0%, #2DB84B 100%); padding: 28px 32px; }
    .header h1 { color: #fff; font-size: 20px; margin: 0; }
    .header p  { color: rgba(255,255,255,.75); font-size: 12px; margin: 4px 0 0; }
    .body { padding: 32px; }
    .greeting { font-size: 15px; font-weight: 600; color: #0D47A1; margin-bottom: 16px; }
    .content  { line-height: 1.8; color: #374151; white-space: pre-wrap; }
    .footer { background: #F8FAFC; border-top: 1px solid #E5E7EB; padding: 16px 32px; font-size: 11px; color: #9CA3AF; text-align: center; }
    .footer strong { color: #0D47A1; }
</style>
</head>
<body>
<div class="wrap">
    <div class="header">
        @php $p = array_merge(\App\Models\Parametre::defauts(), \App\Models\Parametre::tous()); @endphp
        <h1>{{ $p['entreprise_nom'] }}</h1>
        <p>{{ $p['entreprise_slogan'] }}</p>
    </div>
    <div class="body">
        <div class="greeting">Bonjour {{ $nomDestinataire }},</div>
        <div class="content">{{ $message->corps }}</div>
    </div>
    <div class="footer">
        <strong>{{ $p['entreprise_nom'] }}</strong><br>
        @if($p['entreprise_telephone'])Tél : {{ $p['entreprise_telephone'] }} — @endif
        @if($p['entreprise_email']){{ $p['entreprise_email'] }}@endif<br>
        {{ $p['entreprise_ville'] }}, {{ $p['entreprise_pays'] }}
    </div>
</div>
</body>
</html>
