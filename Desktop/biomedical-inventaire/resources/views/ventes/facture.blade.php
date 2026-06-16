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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $vente->numero_facture }} — {{ $p['entreprise_nom'] }}</title>
    <style>
        :root {
            --blue:        #0D47A1;
            --blue-dark:   #072870;
            --blue-light:  #E8EEF8;
            --green:       #2DB84B;
            --green-light: #E8F8EB;
            --gray:        #6B7280;
            --gray-light:  #F8FAFC;
            --border:      #E5E7EB;
            --text:        #1A1A2E;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: var(--text);
            background: #f0f4f8;
        }

        .page {
            max-width: 820px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 32px rgba(0,0,0,.12);
        }

        /* ── Barre supérieure colorée ────────────────────────────── */
        .top-bar {
            height: 6px;
            background: linear-gradient(90deg, var(--blue) 0%, var(--green) 100%);
        }

        /* ── En-tête ────────────────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 28px 40px 22px;
            background: #fff;
            border-bottom: 1px solid var(--border);
        }

        .brand-block {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .brand-logo {
            flex-shrink: 0;
        }
        .brand-logo img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 10px;
            border: 1px solid var(--border);
            padding: 4px;
            background: #fff;
        }
        .brand-logo-fallback {
            width: 90px;
            height: 90px;
            border-radius: 10px;
            background: var(--blue-light);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--blue);
        }
        .brand-logo-fallback span {
            font-weight: 900;
            font-size: 1.8rem;
            color: var(--blue);
        }

        .brand-info {
            padding-top: 4px;
        }
        .brand-info .company-name {
            font-size: 16px;
            font-weight: 800;
            color: var(--blue);
            text-transform: uppercase;
            letter-spacing: .5px;
            line-height: 1.2;
        }
        .brand-info .company-slogan {
            font-size: 9.5px;
            color: var(--green);
            font-style: italic;
            font-weight: 600;
            margin: 2px 0 8px;
        }
        .brand-info .company-details {
            font-size: 10px;
            color: var(--gray);
            line-height: 1.8;
        }

        /* Côté droit — titre facture */
        .invoice-title-block {
            text-align: right;
            padding-top: 2px;
        }
        .invoice-title-block .invoice-word {
            font-size: 36px;
            font-weight: 900;
            color: var(--blue);
            letter-spacing: 4px;
            text-transform: uppercase;
            line-height: 1;
        }
        .invoice-title-block .invoice-num {
            font-size: 15px;
            font-weight: 700;
            color: var(--gray);
            margin: 6px 0 8px;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }
        .badge-statut {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .badge-payee   { background: var(--green-light); color: #065f46; border: 1px solid #a7f3d0; }
        .badge-livree  { background: var(--blue-light);  color: var(--blue); border: 1px solid #bfdbfe; }
        .badge-autre   { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-annulee { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* ── Bandeau émetteur / client ───────────────────────────── */
        .parties-bar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-bottom: 1px solid var(--border);
        }
        .partie {
            padding: 18px 40px;
        }
        .partie:first-child {
            border-right: 1px solid var(--border);
        }
        .partie h4 {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--green);
            font-weight: 700;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid var(--green);
            display: inline-block;
        }
        .partie .party-name {
            font-size: 14px;
            font-weight: 800;
            color: var(--blue);
            margin-bottom: 4px;
        }
        .partie .party-detail {
            font-size: 10.5px;
            color: var(--gray);
            line-height: 1.75;
        }

        /* ── Bandeau méta (dates, mode paiement) ────────────────── */
        .meta-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            background: var(--blue);
            margin: 0;
        }
        .meta-item {
            padding: 12px 20px;
            border-right: 1px solid rgba(255,255,255,.15);
        }
        .meta-item:last-child { border-right: none; }
        .meta-item label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,.6);
            display: block;
            margin-bottom: 3px;
        }
        .meta-item span {
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }

        /* ── Tableau articles ────────────────────────────────────── */
        .table-wrap { padding: 24px 40px 0; }

        table { width: 100%; border-collapse: collapse; }

        thead tr {
            background: var(--blue-dark);
        }
        thead th {
            padding: 10px 10px;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: rgba(255,255,255,.9);
            font-weight: 600;
        }
        thead th:first-child { border-radius: 4px 0 0 0; }
        thead th:last-child  { border-radius: 0 4px 0 0; }

        tbody tr { border-bottom: 1px solid #F1F5F9; }
        tbody tr:nth-child(even) { background: var(--gray-light); }
        tbody tr:hover { background: var(--blue-light); }
        tbody td { padding: 10px 10px; font-size: 11px; color: var(--text); }

        /* Totaux */
        .totaux-wrap {
            display: flex;
            justify-content: flex-end;
            padding: 0 40px 20px;
            margin-top: 0;
        }
        .totaux {
            width: 280px;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }
        .totaux-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 14px;
            border-bottom: 1px solid var(--border);
            font-size: 11px;
        }
        .totaux-row:last-child { border-bottom: none; }
        .totaux-row label { color: var(--gray); }
        .totaux-row span  { font-weight: 600; color: var(--text); }
        .totaux-row.remise label, .totaux-row.remise span { color: #dc2626; }
        .totaux-row.acompte label, .totaux-row.acompte span { color: #059669; }
        .totaux-row.total-ttc {
            background: var(--blue);
            padding: 12px 14px;
        }
        .totaux-row.total-ttc label { color: rgba(255,255,255,.85); font-size: 11px; font-weight: 700; }
        .totaux-row.total-ttc span  { color: #fff; font-size: 15px; font-weight: 800; }
        .totaux-row.net-payer {
            background: #fef2f2;
        }
        .totaux-row.net-payer label, .totaux-row.net-payer span { color: #dc2626; font-weight: 700; }

        /* ── Zone conditions / notes ────────────────────────────── */
        .conditions-wrap {
            margin: 0 40px 24px;
            background: var(--gray-light);
            border-left: 4px solid var(--green);
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 10.5px;
            color: #4B5563;
            line-height: 1.7;
        }
        .conditions-wrap strong { color: var(--blue); }

        /* ── Signature / Cachet ────────────────────────────────── */
        .signature-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin: 0 40px 24px;
        }
        .signature-box {
            border: 1px dashed var(--border);
            border-radius: 6px;
            padding: 10px 14px;
            min-height: 72px;
        }
        .signature-box label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray);
            display: block;
            margin-bottom: 4px;
        }

        /* ── Pied de page ───────────────────────────────────────── */
        .footer {
            background: var(--blue-dark);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer .footer-left {
            color: rgba(255,255,255,.8);
            font-size: 10px;
            line-height: 1.7;
        }
        .footer .footer-left strong {
            color: #fff;
            font-size: 11px;
        }
        .footer .footer-right {
            text-align: right;
            font-size: 9.5px;
            color: rgba(255,255,255,.6);
            line-height: 1.7;
        }
        .footer .footer-right strong {
            color: rgba(255,255,255,.9);
        }

        /* Barre verte décorative en bas */
        .bottom-bar {
            height: 4px;
            background: linear-gradient(90deg, var(--green) 0%, var(--blue) 100%);
        }

        .mentions {
            padding: 10px 40px;
            font-size: 9.5px;
            color: #9CA3AF;
            text-align: center;
            border-top: 1px solid var(--border);
            line-height: 1.6;
        }

        /* ── Filigrane annulé ───────────────────────────────────── */
        .annule-watermark {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 100px;
            color: rgba(220,38,38,.07);
            font-weight: 900;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 0;
            letter-spacing: 8px;
        }

        /* ── Boutons hors impression ────────────────────────────── */
        .no-print {
            background: #fff;
            padding: 12px 40px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--border);
        }
        .btn-print {
            background: var(--blue);
            color: #fff;
            border: none;
            padding: 9px 22px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .2s;
        }
        .btn-print:hover { background: var(--blue-dark); }
        .btn-back {
            color: var(--gray);
            text-decoration: none;
            font-size: 12px;
            padding: 9px 14px;
            border-radius: 6px;
            border: 1px solid var(--border);
            transition: background .2s;
        }
        .btn-back:hover { background: var(--gray-light); }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .page { box-shadow: none; }
        }
    </style>
</head>
<body>

@if($vente->statut === 'annulee')
<div class="annule-watermark">ANNULÉE</div>
@endif

<div class="page">

    {{-- Barre supérieure --}}
    <div class="top-bar"></div>

    {{-- Boutons hors impression --}}
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Imprimer
        </button>
        <a href="{{ route('ventes.show', $vente) }}" class="btn-back">← Retour</a>
    </div>

    {{-- En-tête --}}
    <div class="header">
        {{-- Logo + infos entreprise --}}
        <div class="brand-block">
            <div class="brand-logo">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $p['entreprise_nom'] }}">
                @else
                    <div class="brand-logo-fallback">
                        <span>{{ strtoupper(substr($p['entreprise_nom'], 0, 2)) }}</span>
                    </div>
                @endif
            </div>
            <div class="brand-info">
                <div class="company-name">{{ $p['entreprise_nom'] }}</div>
                @if($p['entreprise_slogan'])
                <div class="company-slogan">{{ $p['entreprise_slogan'] }}</div>
                @endif
                <div class="company-details">
                    @if($p['entreprise_adresse']){{ $p['entreprise_adresse'] }}<br>@endif
                    {{ $p['entreprise_ville'] }}@if($p['entreprise_ville'] && $p['entreprise_pays']), @endif{{ $p['entreprise_pays'] }}<br>
                    @if($p['entreprise_telephone'])<strong>Tél :</strong> {{ $p['entreprise_telephone'] }}<br>@endif
                    @if($p['entreprise_email']){{ $p['entreprise_email'] }}<br>@endif
                    @if($p['entreprise_niu'])<strong>NIU :</strong> {{ $p['entreprise_niu'] }}<br>@endif
                    @if($p['entreprise_rc'])<strong>RC :</strong> {{ $p['entreprise_rc'] }}@endif
                </div>
            </div>
        </div>

        {{-- Titre facture --}}
        <div class="invoice-title-block">
            <div class="invoice-word">Facture</div>
            <div class="invoice-num">{{ $vente->numero_facture }}</div>
            @php
                $badgeClass = match($vente->statut) {
                    'payee'   => 'badge-payee',
                    'livree'  => 'badge-livree',
                    'annulee' => 'badge-annulee',
                    default   => 'badge-autre',
                };
            @endphp
            <span class="badge-statut {{ $badgeClass }}">{{ $vente->statut_label }}</span>
        </div>
    </div>

    {{-- Émetteur / Facturé à --}}
    <div class="parties-bar">
        <div class="partie">
            <h4>Émetteur</h4>
            <div class="party-name">{{ $p['entreprise_nom'] }}</div>
            <div class="party-detail">
                @if($p['entreprise_adresse']){{ $p['entreprise_adresse'] }}<br>@endif
                {{ $p['entreprise_ville'] }}@if($p['entreprise_ville']), @endif{{ $p['entreprise_pays'] }}
            </div>
        </div>
        <div class="partie">
            <h4>Facturé à</h4>
            <div class="party-name">{{ $vente->client->nom }}</div>
            <div class="party-detail">
                {{ $vente->client->type_label }}<br>
                @if($vente->client->adresse){{ $vente->client->adresse }}<br>@endif
                @if($vente->client->ville){{ $vente->client->ville }}, @endif{{ $vente->client->pays ?? '' }}<br>
                @if($vente->client->telephone)Tél : {{ $vente->client->telephone }}<br>@endif
                @if($vente->client->numero_contribuable)N° : {{ $vente->client->numero_contribuable }}@endif
            </div>
        </div>
    </div>

    {{-- Bandeau méta --}}
    <div class="meta-bar">
        <div class="meta-item">
            <label>Date de vente</label>
            <span>{{ $vente->date_vente->format('d/m/Y') }}</span>
        </div>
        <div class="meta-item">
            <label>Mode de paiement</label>
            <span>{{ $vente->mode_paiement_label }}</span>
        </div>
        <div class="meta-item">
            @if($vente->date_echeance)
                <label>Échéance</label>
                <span>{{ $vente->date_echeance->format('d/m/Y') }}</span>
            @elseif($vente->date_livraison_prevue)
                <label>Livraison prévue</label>
                <span>{{ $vente->date_livraison_prevue->format('d/m/Y') }}</span>
            @else
                <label>Vendeur</label>
                <span>{{ $vente->user->name }}</span>
            @endif
        </div>
    </div>

    {{-- Tableau articles --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width:4%" class="text-center">#</th>
                    <th style="width:34%">Désignation</th>
                    <th style="width:15%">Référence</th>
                    <th class="text-center" style="width:7%">Qté</th>
                    <th class="text-right" style="width:16%">P.U. HT ({{ $p['monnaie_symbole'] }})</th>
                    <th class="text-center" style="width:8%">Remise</th>
                    <th class="text-right" style="width:16%">Total HT ({{ $p['monnaie_symbole'] }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vente->lignes as $i => $ligne)
                <tr>
                    <td class="text-center" style="color:#9CA3AF;font-size:10px">{{ $i + 1 }}</td>
                    <td><strong>{{ $ligne->designation_snapshot }}</strong></td>
                    <td style="color:#6B7280;font-size:10px;font-family:'Courier New',monospace">{{ $ligne->reference_snapshot }}</td>
                    <td class="text-center">{{ $ligne->quantite }}</td>
                    <td class="text-right">{{ number_format($ligne->prix_unitaire_ht, 0, ',', ' ') }}</td>
                    <td class="text-center" style="color:#9CA3AF">{{ $ligne->remise > 0 ? $ligne->remise . '%' : '—' }}</td>
                    <td class="text-right"><strong>{{ number_format($ligne->total_ht, 0, ',', ' ') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totaux --}}
    <div class="totaux-wrap">
        <div class="totaux">
            <div class="totaux-row">
                <label>Sous-total HT</label>
                <span>{{ number_format($vente->sous_total_ht, 0, ',', ' ') }} {{ $p['monnaie_symbole'] }}</span>
            </div>
            @if($vente->montant_remise > 0)
            <div class="totaux-row remise">
                <label>Remise ({{ $vente->remise_globale }}%)</label>
                <span>- {{ number_format($vente->montant_remise, 0, ',', ' ') }}</span>
            </div>
            @endif
            <div class="totaux-row">
                <label>TVA ({{ $vente->tva }}%)</label>
                <span>{{ number_format($vente->montant_tva, 0, ',', ' ') }} {{ $p['monnaie_symbole'] }}</span>
            </div>
            @if($vente->montant_paye > 0 && $vente->montant_paye < $vente->total_ttc)
            <div class="totaux-row acompte">
                <label>Acompte versé</label>
                <span>- {{ number_format($vente->montant_paye, 0, ',', ' ') }}</span>
            </div>
            @endif
            <div class="totaux-row total-ttc">
                <label>TOTAL TTC</label>
                <span>{{ number_format($vente->total_ttc, 0, ',', ' ') }} {{ $p['monnaie_symbole'] }}</span>
            </div>
            @if(isset($vente->reste_a_payer) && $vente->reste_a_payer > 0)
            <div class="totaux-row net-payer">
                <label>Net à payer</label>
                <span>{{ number_format($vente->reste_a_payer, 0, ',', ' ') }} {{ $p['monnaie_symbole'] }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Conditions & Notes --}}
    @if($p['facture_conditions'] || $vente->notes)
    <div class="conditions-wrap">
        @if($vente->notes)
            <p style="margin-bottom:3px"><strong>Notes :</strong> {{ $vente->notes }}</p>
        @endif
        @if($p['facture_conditions'])
            <p>{{ $p['facture_conditions'] }}</p>
        @endif
    </div>
    @endif

    {{-- Zone signature --}}
    <div class="signature-wrap">
        <div class="signature-box">
            <label>Signature & Cachet de l'émetteur</label>
        </div>
        <div class="signature-box">
            <label>Bon pour accord — Signature du client</label>
        </div>
    </div>

    {{-- Pied de page --}}
    <div class="footer">
        <div class="footer-left">
            <strong>{{ $p['entreprise_nom'] }}</strong><br>
            @if($p['entreprise_niu'])NIU : {{ $p['entreprise_niu'] }} &nbsp;|&nbsp; @endif
            @if($p['entreprise_rc'])RC : {{ $p['entreprise_rc'] }}<br>@endif
            @if($p['entreprise_telephone'])Tél : {{ $p['entreprise_telephone'] }} &nbsp;@endif
            @if($p['entreprise_email']) — {{ $p['entreprise_email'] }}@endif
            @if($p['entreprise_site_web'])<br>{{ $p['entreprise_site_web'] }}@endif
        </div>
        <div class="footer-right">
            Document généré le <strong>{{ now()->format('d/m/Y à H:i') }}</strong><br>
            Vendeur : <strong>{{ $vente->user->name }}</strong><br>
            <span style="font-size:8.5px;color:rgba(255,255,255,.4)">{{ $vente->numero_facture }}</span>
        </div>
    </div>

    @if($p['facture_mentions'] || $p['facture_pied'])
    <div class="mentions">
        @if($p['facture_mentions']){{ $p['facture_mentions'] }}@endif
        @if($p['facture_pied'])<br><strong>{{ $p['facture_pied'] }}</strong>@endif
    </div>
    @endif

    {{-- Barre inférieure --}}
    <div class="bottom-bar"></div>

</div>
</body>
</html>
