@php
    use App\Models\Parametre;
    $p = array_merge(Parametre::defauts(), Parametre::tous());
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $vente->numero_facture }} — {{ $p['entreprise_nom'] }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

        .page { max-width: 800px; margin: 0 auto; padding: 30px 40px; }

        /* En-tête */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 3px solid #1a3a5c; padding-bottom: 20px; }
        .brand h1 { font-size: 22px; color: #1a3a5c; }
        .brand p { font-size: 10px; color: #666; margin-top: 2px; }
        .facture-title { text-align: right; }
        .facture-title h2 { font-size: 28px; color: #1a3a5c; text-transform: uppercase; letter-spacing: 2px; }
        .facture-title p { font-size: 11px; color: #555; }
        .badge-statut { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }
        .badge-payee { background: #d1fae5; color: #065f46; }
        .badge-autre { background: #fef3c7; color: #92400e; }

        /* Parties émetteur / client */
        .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 25px; }
        .partie h4 { font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 1px; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; }
        .partie p { font-size: 11px; line-height: 1.7; color: #333; }
        .partie strong { color: #1a1a2e; font-size: 13px; }

        /* Dates */
        .meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; background: #f8fafc; padding: 12px 15px; border-radius: 8px; margin-bottom: 25px; }
        .meta-item label { font-size: 9px; text-transform: uppercase; color: #999; letter-spacing: 1px; display: block; }
        .meta-item span { font-size: 12px; font-weight: bold; color: #1a3a5c; }

        /* Tableau articles */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #1a3a5c; color: #fff; }
        thead th { padding: 10px 8px; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
        tbody tr { border-bottom: 1px solid #f0f0f0; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td { padding: 9px 8px; font-size: 11px; }
        tfoot tr { border-top: 2px solid #e5e7eb; }
        tfoot td { padding: 7px 8px; font-size: 11px; }
        tfoot tr.total-ttc td { background: #1a3a5c; color: #fff; font-size: 14px; font-weight: bold; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Totaux */
        .totaux { margin-left: auto; width: 260px; }

        /* Pied de page */
        .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; font-size: 10px; color: #999; }
        .footer .mention { font-size: 9px; color: #aaa; text-align: center; margin-top: 20px; }

        /* Filigrane annulé */
        .annule-watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 100px; color: rgba(220,38,38,.08); font-weight: bold; text-transform: uppercase; pointer-events: none; z-index: 0; }

        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page { padding: 15px 20px; }
        }
    </style>
</head>
<body>

@if($vente->statut === 'annulee')
<div class="annule-watermark">ANNULÉE</div>
@endif

<div class="page">

    {{-- Bouton impression --}}
    <div class="no-print" style="text-align:right;margin-bottom:15px">
        <button onclick="window.print()" style="background:#1a3a5c;color:#fff;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:13px">
            🖨 Imprimer
        </button>
        <a href="{{ route('ventes.show', $vente) }}"
           style="margin-left:8px;color:#666;text-decoration:none;font-size:12px">← Retour</a>
    </div>

    {{-- En-tête --}}
    <div class="header">
        <div class="brand">
            @if($p['entreprise_logo'])
                <img src="{{ Storage::url($p['entreprise_logo']) }}"
                     style="max-height:70px;max-width:220px;object-fit:contain;margin-bottom:6px;display:block">
            @else
                <h1>🏥 {{ $p['entreprise_nom'] }}</h1>
            @endif
            <p>
                {{ $p['entreprise_slogan'] }}<br>
                @if($p['entreprise_adresse']){{ $p['entreprise_adresse'] }}<br>@endif
                {{ $p['entreprise_ville'] }}@if($p['entreprise_ville'] && $p['entreprise_pays']), @endif{{ $p['entreprise_pays'] }}<br>
                @if($p['entreprise_telephone'])Tél : {{ $p['entreprise_telephone'] }}<br>@endif
                @if($p['entreprise_email']){{ $p['entreprise_email'] }}@endif
            </p>
        </div>
        <div class="facture-title">
            <h2>Facture</h2>
            <p style="font-size:16px;font-weight:bold;color:#1a3a5c">{{ $vente->numero_facture }}</p>
            <div class="badge-statut {{ $vente->statut === 'payee' ? 'badge-payee' : 'badge-autre' }}">
                {{ $vente->statut_label }}
            </div>
        </div>
    </div>

    {{-- Émetteur / Client --}}
    <div class="parties">
        <div class="partie">
            <h4>Émetteur</h4>
            <p>
                <strong>{{ $p['entreprise_nom'] }}</strong><br>
                @if($p['entreprise_adresse']){{ $p['entreprise_adresse'] }}<br>@endif
                {{ $p['entreprise_ville'] }}@if($p['entreprise_ville']), @endif{{ $p['entreprise_pays'] }}<br>
                @if($p['entreprise_niu'])NIU : {{ $p['entreprise_niu'] }}<br>@endif
                @if($p['entreprise_rc'])RC : {{ $p['entreprise_rc'] }}<br>@endif
                @if($p['entreprise_telephone'])Tél : {{ $p['entreprise_telephone'] }}<br>@endif
                @if($p['entreprise_email']){{ $p['entreprise_email'] }}@endif
            </p>
        </div>
        <div class="partie">
            <h4>Facturé à</h4>
            <p>
                <strong>{{ $vente->client->nom }}</strong><br>
                {{ $vente->client->type_label }}<br>
                @if($vente->client->adresse){{ $vente->client->adresse }}<br>@endif
                @if($vente->client->ville){{ $vente->client->ville }}, @endif{{ $vente->client->pays }}<br>
                @if($vente->client->numero_contribuable)N° : {{ $vente->client->numero_contribuable }}<br>@endif
                @if($vente->client->telephone)Tél : {{ $vente->client->telephone }}@endif
            </p>
        </div>
    </div>

    {{-- Méta --}}
    <div class="meta">
        <div class="meta-item">
            <label>Date de vente</label>
            <span>{{ $vente->date_vente->format('d/m/Y') }}</span>
        </div>
        <div class="meta-item">
            <label>Mode de paiement</label>
            <span>{{ $vente->mode_paiement_label }}</span>
        </div>
        @if($vente->date_echeance)
        <div class="meta-item">
            <label>Échéance</label>
            <span>{{ $vente->date_echeance->format('d/m/Y') }}</span>
        </div>
        @elseif($vente->date_livraison_prevue)
        <div class="meta-item">
            <label>Livraison prévue</label>
            <span>{{ $vente->date_livraison_prevue->format('d/m/Y') }}</span>
        </div>
        @else
        <div class="meta-item">
            <label>Vendeur</label>
            <span>{{ $vente->user->name }}</span>
        </div>
        @endif
    </div>

    {{-- Tableau articles --}}
    <table>
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:35%">Désignation</th>
                <th style="width:15%">Référence</th>
                <th class="text-center" style="width:8%">Qté</th>
                <th class="text-right" style="width:15%">P.U. HT (FCFA)</th>
                <th class="text-center" style="width:8%">Remise</th>
                <th class="text-right" style="width:14%">Total HT (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vente->lignes as $i => $ligne)
            <tr>
                <td class="text-center" style="color:#999">{{ $i + 1 }}</td>
                <td><strong>{{ $ligne->designation_snapshot }}</strong></td>
                <td style="color:#666;font-size:10px">{{ $ligne->reference_snapshot }}</td>
                <td class="text-center">{{ $ligne->quantite }}</td>
                <td class="text-right">{{ number_format($ligne->prix_unitaire_ht, 0, ',', ' ') }}</td>
                <td class="text-center" style="color:#999">{{ $ligne->remise > 0 ? $ligne->remise . '%' : '—' }}</td>
                <td class="text-right"><strong>{{ number_format($ligne->total_ht, 0, ',', ' ') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right" style="color:#666">Sous-total HT</td>
                <td class="text-right">{{ number_format($vente->sous_total_ht, 0, ',', ' ') }}</td>
            </tr>
            @if($vente->montant_remise > 0)
            <tr>
                <td colspan="6" class="text-right" style="color:#dc2626">Remise globale ({{ $vente->remise_globale }}%)</td>
                <td class="text-right" style="color:#dc2626">- {{ number_format($vente->montant_remise, 0, ',', ' ') }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="6" class="text-right" style="color:#666">TVA ({{ $vente->tva }}%)</td>
                <td class="text-right">{{ number_format($vente->montant_tva, 0, ',', ' ') }}</td>
            </tr>
            @if($vente->montant_paye > 0 && $vente->montant_paye < $vente->total_ttc)
            <tr>
                <td colspan="6" class="text-right" style="color:#059669">Acompte versé</td>
                <td class="text-right" style="color:#059669">- {{ number_format($vente->montant_paye, 0, ',', ' ') }}</td>
            </tr>
            @endif
            <tr class="total-ttc">
                <td colspan="6" class="text-right">TOTAL TTC</td>
                <td class="text-right">{{ number_format($vente->total_ttc, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if($vente->reste_a_payer > 0)
            <tr>
                <td colspan="6" class="text-right" style="color:#dc2626;font-weight:bold">Net à payer</td>
                <td class="text-right" style="color:#dc2626;font-weight:bold">{{ number_format($vente->reste_a_payer, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
        </tfoot>
    </table>

    {{-- Conditions --}}
    @if($p['facture_conditions'] || $vente->notes)
    <div style="background:#f8fafc;padding:10px 15px;border-radius:6px;margin-bottom:15px;font-size:11px;color:#555">
        @if($vente->notes)<p style="margin-bottom:4px"><strong>Notes :</strong> {{ $vente->notes }}</p>@endif
        @if($p['facture_conditions'])<p style="margin:0">{{ $p['facture_conditions'] }}</p>@endif
    </div>
    @endif

    {{-- Pied --}}
    <div class="footer">
        <div>
            <strong>{{ $p['entreprise_nom'] }}</strong><br>
            @if($p['entreprise_rc'])RC : {{ $p['entreprise_rc'] }}@endif
            @if($p['entreprise_rc'] && $p['entreprise_niu']) | @endif
            @if($p['entreprise_niu'])NIU : {{ $p['entreprise_niu'] }}@endif
            @if($p['entreprise_site_web'])<br>{{ $p['entreprise_site_web'] }}@endif
        </div>
        <div style="text-align:right">
            Document généré le {{ now()->format('d/m/Y à H:i') }}<br>
            Vendeur : {{ $vente->user->name }}
        </div>
    </div>
    @if($p['facture_mentions'] || $p['facture_pied'])
    <div class="mention">
        {{ $p['facture_mentions'] }}
        @if($p['facture_pied'])<br><strong>{{ $p['facture_pied'] }}</strong>@endif
    </div>
    @endif

</div>
</body>
</html>
