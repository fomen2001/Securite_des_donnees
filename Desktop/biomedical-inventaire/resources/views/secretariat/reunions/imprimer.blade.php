@php
    use App\Models\Parametre;
    $p = array_merge(Parametre::defauts(), Parametre::tous());
    $logoFile = $p['entreprise_logo'] ?? null;
    $logoOk   = $logoFile && file_exists(public_path('images/' . $logoFile));
    $logoUrl  = $logoOk ? asset('images/' . $logoFile) : null;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $reunion->reference }} — {{ $reunion->titre }}</title>
    <style>
        :root { --blue:#0D47A1; --green:#2DB84B; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',Arial,sans-serif; font-size:12px; color:#1a1a2e; background:#f0f4f8; }
        .page { max-width:800px; margin:0 auto; background:#fff; box-shadow:0 4px 20px rgba(0,0,0,.1); }
        .top-bar { height:5px; background:linear-gradient(90deg,var(--blue),var(--green)); }

        .header { display:flex; justify-content:space-between; align-items:center; padding:20px 32px; border-bottom:1px solid #e5e7eb; }
        .header-logo img { width:70px; height:70px; object-fit:contain; border:1px solid #e5e7eb; border-radius:8px; padding:3px; }
        .header-company { margin-left:12px; }
        .header-company .name { font-size:14px; font-weight:800; color:var(--blue); }
        .header-company .slogan { font-size:9px; color:var(--green); font-style:italic; }
        .header-title { text-align:right; }
        .header-title h1 { font-size:18px; font-weight:900; color:var(--blue); text-transform:uppercase; letter-spacing:2px; }
        .header-title .ref { font-family:monospace; color:#6b7280; font-size:11px; margin-top:2px; }

        .meta { background:var(--blue); display:grid; grid-template-columns:repeat(4,1fr); }
        .meta-item { padding:10px 16px; border-right:1px solid rgba(255,255,255,.15); }
        .meta-item:last-child { border-right:none; }
        .meta-item label { font-size:8px; text-transform:uppercase; letter-spacing:1px; color:rgba(255,255,255,.6); display:block; margin-bottom:2px; }
        .meta-item span  { font-size:11px; font-weight:700; color:#fff; }

        .section { padding:16px 32px; border-bottom:1px solid #f1f5f9; }
        .section h3 { font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--green); font-weight:700;
                      border-left:3px solid var(--green); padding-left:8px; margin-bottom:10px; }
        .section .content { line-height:1.8; color:#374151; white-space:pre-wrap; }

        .participants-table { width:100%; border-collapse:collapse; }
        .participants-table th { background:#f8fafc; font-size:9px; text-transform:uppercase; letter-spacing:.5px;
                                  color:var(--blue); padding:7px 10px; border-bottom:2px solid #e5e7eb; text-align:left; }
        .participants-table td { padding:7px 10px; border-bottom:1px solid #f1f5f9; font-size:11px; }
        .badge-present { background:#dcfce7; color:#166534; padding:2px 8px; border-radius:20px; font-size:9px; font-weight:600; }
        .badge-absent  { background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:20px; font-size:9px; font-weight:600; }

        .signatures { display:grid; grid-template-columns:1fr 1fr; gap:24px; padding:16px 32px 24px; }
        .sig-box { border:1px dashed #d1d5db; border-radius:6px; padding:10px 14px; min-height:64px; }
        .sig-box label { font-size:9px; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; }

        .footer { background:#072870; padding:14px 32px; display:flex; justify-content:space-between; align-items:center; }
        .footer .fl { color:rgba(255,255,255,.8); font-size:10px; line-height:1.7; }
        .footer .fl strong { color:#fff; }
        .footer .fr { font-size:9px; color:rgba(255,255,255,.5); text-align:right; }
        .bottom-bar { height:4px; background:linear-gradient(90deg,var(--green),var(--blue)); }

        .no-print { background:#fff; padding:12px 32px; text-align:right; border-bottom:1px solid #e5e7eb; }
        .btn-p { background:var(--blue); color:#fff; border:none; padding:8px 20px; border-radius:6px; cursor:pointer; font-size:13px; font-weight:600; }
        .btn-r { color:#6b7280; text-decoration:none; margin-right:10px; font-size:12px; }

        @media print {
            body { background:#fff; }
            .no-print { display:none; }
            .page { box-shadow:none; }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="top-bar"></div>

    <div class="no-print">
        <a href="{{ route('secretariat.reunions.show', $reunion) }}" class="btn-r">← Retour</a>
        <button onclick="window.print()" class="btn-p">🖨 Imprimer</button>
    </div>

    <div class="header">
        <div style="display:flex;align-items:center">
            @if($logoUrl)
                <div class="header-logo"><img src="{{ $logoUrl }}" alt="{{ $p['entreprise_nom'] }}"></div>
            @endif
            <div class="header-company" style="{{ $logoUrl ? '' : 'margin-left:0' }}">
                <div class="name">{{ $p['entreprise_nom'] }}</div>
                @if($p['entreprise_slogan'])<div class="slogan">{{ $p['entreprise_slogan'] }}</div>@endif
                <div style="font-size:9px;color:#9ca3af;margin-top:4px">
                    @if($p['entreprise_ville']){{ $p['entreprise_ville'] }}, @endif{{ $p['entreprise_pays'] }}
                </div>
            </div>
        </div>
        <div class="header-title">
            <h1>Rapport de réunion</h1>
            <div class="ref">{{ $reunion->reference }}</div>
            @if($reunion->statut === 'finalise')
                <span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:20px;font-size:9px;font-weight:700;display:inline-block;margin-top:4px">FINALISÉ</span>
            @else
                <span style="background:#f3f4f6;color:#6b7280;padding:2px 10px;border-radius:20px;font-size:9px;font-weight:700;display:inline-block;margin-top:4px">BROUILLON</span>
            @endif
        </div>
    </div>

    <div class="meta">
        <div class="meta-item"><label>Date</label><span>{{ $reunion->date_reunion->format('d/m/Y') }}</span></div>
        <div class="meta-item"><label>Heure</label><span>{{ $reunion->date_reunion->format('H:i') }}</span></div>
        <div class="meta-item"><label>Lieu</label><span>{{ $reunion->lieu ?? '—' }}</span></div>
        <div class="meta-item"><label>Type</label><span>{{ $reunion->type_label }}</span></div>
    </div>

    <div class="section" style="padding-top:20px">
        <h3>Titre</h3>
        <div style="font-size:15px;font-weight:700;color:#1a1a2e">{{ $reunion->titre }}</div>
    </div>

    @if($reunion->ordre_du_jour)
    <div class="section">
        <h3>Ordre du jour</h3>
        <div class="content">{{ $reunion->ordre_du_jour }}</div>
    </div>
    @endif

    {{-- Participants --}}
    <div class="section">
        <h3>Participants ({{ $reunion->participants->count() }})</h3>
        <table class="participants-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Fonction</th>
                    <th>Entreprise</th>
                    <th>Présence</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reunion->participants as $part)
                <tr>
                    <td><strong>{{ $part->nom }}</strong></td>
                    <td>{{ $part->fonction ?? '—' }}</td>
                    <td>{{ $part->entreprise ?? '—' }}</td>
                    <td>
                        @if($part->present)
                            <span class="badge-present">✓ Présent</span>
                        @else
                            <span class="badge-absent">✗ Absent</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Compte-rendu</h3>
        <div class="content">{{ $reunion->compte_rendu }}</div>
    </div>

    @if($reunion->decisions)
    <div class="section">
        <h3>Décisions prises</h3>
        <div class="content">{{ $reunion->decisions }}</div>
    </div>
    @endif

    @if($reunion->actions_a_suivre)
    <div class="section">
        <h3>Actions à suivre</h3>
        <div class="content">{{ $reunion->actions_a_suivre }}</div>
    </div>
    @endif

    <div class="signatures">
        <div class="sig-box"><label>Signature du rédacteur ({{ $reunion->user->name }})</label></div>
        <div class="sig-box"><label>Signature du président de séance</label></div>
    </div>

    <div class="footer">
        <div class="fl">
            <strong>{{ $p['entreprise_nom'] }}</strong><br>
            @if($p['entreprise_niu'])NIU : {{ $p['entreprise_niu'] }} — @endif
            @if($p['entreprise_rc'])RC : {{ $p['entreprise_rc'] }}@endif
        </div>
        <div class="fr">
            Document généré le {{ now()->format('d/m/Y à H:i') }}<br>
            Rédacteur : {{ $reunion->user->name }}
        </div>
    </div>
    <div class="bottom-bar"></div>
</div>
</body>
</html>
