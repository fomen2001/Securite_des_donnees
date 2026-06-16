@extends('layouts.app')

@section('title', 'Bilan ' . $bilan->exercice)
@section('page-title', 'Bilan comptable ' . $bilan->exercice . ' — SYSCOHADA')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item"><a href="{{ route('impots.bilan.index') }}">Bilan</a></li>
    <li class="breadcrumb-item active">{{ $bilan->exercice }}</li>
@endsection

@push('styles')
<style>
@media print {
    .sidebar, .topbar, .btn, .breadcrumb, .d-flex.gap-2 { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .card { border: 1px solid #ccc !important; }
}
.bilan-header { background: #1a3a5c; color: white; padding: .5rem 1rem; font-weight: 600; }
.bilan-section { background: #f0f4f8; padding: .3rem 1rem; font-weight: 600; font-size: .85rem; }
.bilan-row td { font-size: .88rem; padding: .3rem .75rem; }
.bilan-total td { font-weight: bold; background: #e8f4fd; }
.bilan-grand-total td { font-weight: bold; font-size: 1rem; background: #1a3a5c; color: white; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-{{ $bilan->statut_badge }} fs-6 me-2">{{ $bilan->statut_label }}</span>
        @if(!$bilan->equilibre)
            <span class="badge bg-danger">⚠ Déséquilibré</span>
        @else
            <span class="badge bg-success"><i class="bi bi-check"></i> Équilibré</span>
        @endif
    </div>
    <div class="d-flex gap-2">
        @if($bilan->statut === 'brouillon')
        <form action="{{ route('impots.bilan.valider', $bilan->exercice) }}" method="POST">
            @csrf <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Valider</button>
        </form>
        @endif
        @if($bilan->statut === 'valide')
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalDepot">
            <i class="bi bi-send me-1"></i>Marquer déposé (DSF)
        </button>
        @endif
        <a href="{{ route('impots.bilan.create', ['exercice' => $bilan->exercice]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>Modifier
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Imprimer</button>
    </div>
</div>

@php
function af($v) { return number_format(abs($v), 0, ',', ' ') . ' FCFA'; }
function signed($v) { return ($v >= 0 ? '' : '(') . number_format(abs($v), 0, ',', ' ') . ' FCFA' . ($v < 0 ? ')' : ''); }
@endphp

<div class="row g-4">
{{-- BILAN ACTIF / PASSIF ─────────────────────────────────────── --}}
<div class="col-lg-6">
<div class="card">
    <div class="bilan-header">BILAN ACTIF — Exercice {{ $bilan->exercice }}</div>
    <table class="table table-sm mb-0">
        <thead><tr><th>Rubrique</th><th class="text-end">Montant (FCFA)</th></tr></thead>
        <tbody>
            <tr><td colspan="2" class="bilan-section">A — Actif immobilisé</td></tr>
            <tr class="bilan-row"><td class="ps-4">Immobilisations incorporelles</td><td class="text-end">{{ af($bilan->immob_incorporelles) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Immobilisations corporelles</td><td class="text-end">{{ af($bilan->immob_corporelles) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Immobilisations financières</td><td class="text-end">{{ af($bilan->immob_financieres) }}</td></tr>
            <tr class="bilan-total"><td>TOTAL A</td><td class="text-end">{{ af($bilan->total_actif_immobilise) }}</td></tr>

            <tr><td colspan="2" class="bilan-section">B — Actif circulant</td></tr>
            <tr class="bilan-row"><td class="ps-4">Stocks et encours</td><td class="text-end">{{ af($bilan->stocks) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Créances clients</td><td class="text-end">{{ af($bilan->creances_clients) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">TVA récupérable</td><td class="text-end">{{ af($bilan->tva_recuperable) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Autres créances</td><td class="text-end">{{ af($bilan->autres_creances) }}</td></tr>
            <tr class="bilan-total"><td>TOTAL B</td><td class="text-end">{{ af($bilan->total_actif_circulant) }}</td></tr>

            <tr><td colspan="2" class="bilan-section">C — Trésorerie-Actif</td></tr>
            <tr class="bilan-row"><td class="ps-4">Banques, CCP, Caisse</td><td class="text-end">{{ af($bilan->banques_caisse) }}</td></tr>
            <tr class="bilan-total"><td>TOTAL C</td><td class="text-end">{{ af($bilan->banques_caisse) }}</td></tr>

            <tr class="bilan-grand-total"><td>TOTAL ACTIF (A+B+C)</td><td class="text-end">{{ af($bilan->total_actif) }}</td></tr>
        </tbody>
    </table>
</div>
</div>

<div class="col-lg-6">
<div class="card">
    <div class="bilan-header" style="background:#198754">BILAN PASSIF — Exercice {{ $bilan->exercice }}</div>
    <table class="table table-sm mb-0">
        <thead><tr><th>Rubrique</th><th class="text-end">Montant (FCFA)</th></tr></thead>
        <tbody>
            <tr><td colspan="2" class="bilan-section">I — Capitaux propres</td></tr>
            <tr class="bilan-row"><td class="ps-4">Capital social</td><td class="text-end">{{ af($bilan->capital_social) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Réserves</td><td class="text-end">{{ af($bilan->reserves) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Report à nouveau</td><td class="text-end">{{ signed($bilan->report_a_nouveau) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Résultat net {{ $bilan->exercice }}</td>
                <td class="text-end {{ $bilan->resultat_exercice >= 0 ? 'text-success' : 'text-danger' }}">{{ signed($bilan->resultat_exercice) }}</td></tr>
            <tr class="bilan-total"><td>TOTAL I</td><td class="text-end">{{ signed($bilan->total_capitaux_propres) }}</td></tr>

            <tr><td colspan="2" class="bilan-section">II — Dettes financières (LT)</td></tr>
            <tr class="bilan-row"><td class="ps-4">Emprunts à long terme</td><td class="text-end">{{ af($bilan->emprunts_long_terme) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Autres dettes financières</td><td class="text-end">{{ af($bilan->autres_dettes_financieres) }}</td></tr>
            <tr class="bilan-total"><td>TOTAL II</td><td class="text-end">{{ af($bilan->total_dettes_financieres) }}</td></tr>

            <tr><td colspan="2" class="bilan-section">E — Passif circulant</td></tr>
            <tr class="bilan-row"><td class="ps-4">Dettes fournisseurs</td><td class="text-end">{{ af($bilan->dettes_fournisseurs) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Dettes fiscales</td><td class="text-end">{{ af($bilan->dettes_fiscales) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Dettes sociales</td><td class="text-end">{{ af($bilan->dettes_sociales) }}</td></tr>
            <tr class="bilan-row"><td class="ps-4">Autres dettes CT</td><td class="text-end">{{ af($bilan->autres_dettes_court_terme) }}</td></tr>
            <tr class="bilan-total"><td>TOTAL E</td><td class="text-end">{{ af($bilan->total_passif_circulant) }}</td></tr>

            <tr class="bilan-grand-total" style="background:#198754"><td>TOTAL PASSIF (I+II+E)</td><td class="text-end">{{ af($bilan->total_passif) }}</td></tr>
        </tbody>
    </table>
</div>
</div>

{{-- COMPTE DE RÉSULTAT ──────────────────────────────────────── --}}
<div class="col-12">
<div class="card">
    <div class="bilan-header" style="background:#6c757d">COMPTE DE RÉSULTAT — Exercice {{ $bilan->exercice }}</div>
    <div class="row g-0">
        <div class="col-md-6 border-end">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>CHARGES</th><th class="text-end">FCFA</th></tr></thead>
                <tbody>
                    <tr class="bilan-row"><td>Achats consommés</td><td class="text-end">{{ af($bilan->achats_consommes) }}</td></tr>
                    <tr class="bilan-row"><td>Charges de personnel</td><td class="text-end">{{ af($bilan->charges_personnel) }}</td></tr>
                    <tr class="bilan-row"><td>Dotations aux amortissements</td><td class="text-end">{{ af($bilan->dotations_amortissements) }}</td></tr>
                    <tr class="bilan-row"><td>Autres charges exploitation</td><td class="text-end">{{ af($bilan->autres_charges_exploitation) }}</td></tr>
                    <tr class="bilan-row"><td>Charges financières</td><td class="text-end">{{ af($bilan->charges_financieres) }}</td></tr>
                    <tr class="bilan-row"><td>IS de l'exercice</td><td class="text-end">{{ af($bilan->is_exerce) }}</td></tr>
                    @if($bilan->resultat_net < 0)
                    <tr class="bilan-row text-danger fw-bold"><td>Perte de l'exercice</td><td class="text-end">{{ af($bilan->resultat_net) }}</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>PRODUITS</th><th class="text-end">FCFA</th></tr></thead>
                <tbody>
                    <tr class="bilan-row"><td>Chiffre d'affaires HT</td><td class="text-end">{{ af($bilan->chiffre_affaires) }}</td></tr>
                    <tr class="bilan-row"><td>Autres produits exploitation</td><td class="text-end">{{ af($bilan->autres_produits) }}</td></tr>
                    <tr class="bilan-row"><td>Produits financiers</td><td class="text-end">{{ af($bilan->produits_financiers) }}</td></tr>
                    @if($bilan->resultat_net >= 0)
                    <tr class="bilan-row text-success fw-bold"><td>Bénéfice net</td><td class="text-end">{{ af($bilan->resultat_net) }}</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-center fw-bold {{ $bilan->resultat_net >= 0 ? 'text-success' : 'text-danger' }}">
        RÉSULTAT NET {{ $bilan->exercice }} :
        {{ $bilan->resultat_net >= 0 ? 'BÉNÉFICE' : 'PERTE' }} de
        {{ af($bilan->resultat_net) }}
    </div>
</div>
</div>
</div>{{-- row --}}

{{-- Modal dépôt DSF --}}
<div class="modal fade" id="modalDepot" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Dépôt DSF à la DGI</h5></div>
            <form action="{{ route('impots.bilan.deposer', $bilan->exercice) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Date de dépôt <span class="text-danger">*</span></label>
                    <input type="date" name="date_depot" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
