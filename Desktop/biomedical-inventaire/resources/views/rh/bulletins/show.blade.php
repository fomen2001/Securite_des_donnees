@extends('layouts.app')

@section('title', 'Bulletin ' . $bulletin->numero)
@section('page-title', 'Bulletin de paie')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.bulletins.index') }}">Bulletins</a></li>
    <li class="breadcrumb-item active">{{ $bulletin->numero }}</li>
@endsection

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .sidebar { display: none !important; }
    .topbar { display: none !important; }
    .p-4 { padding: 0 !important; }
    .bulletin-card { box-shadow: none !important; border: none !important; }
}
.bulletin-card { max-width: 860px; margin: auto; }
.bul-header { background: #1a3a5c; color: white; }
.bul-table th { background: #f0f4f8; font-weight: 600; }
.bul-net { background: #198754; color: white; font-size: 1.2rem; }
</style>
@endpush

@section('content')

{{-- Actions --}}
<div class="d-flex gap-2 mb-4 no-print">
    <button onclick="window.print()" class="btn btn-outline-secondary">
        <i class="bi bi-printer me-1"></i>Imprimer
    </button>

    @if($bulletin->statut === 'brouillon')
    @can('rh.bulletins.valider')
    <form action="{{ route('rh.bulletins.valider', $bulletin) }}" method="POST">
        @csrf
        <button class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Valider le bulletin</button>
    </form>
    @endcan
    @endif

    @if($bulletin->statut !== 'paye')
    @can('rh.bulletins.payer')
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPaiement">
        <i class="bi bi-cash me-1"></i>Marquer comme payé
    </button>
    @endcan
    @endif

    <span class="badge bg-{{ $bulletin->statut_badge }} fs-6 align-self-center ms-auto">
        {{ ucfirst($bulletin->statut) }}
    </span>
</div>

{{-- Bulletin de paie --}}
<div class="card bulletin-card shadow-sm">

    {{-- En-tête société --}}
    @php $entreprise = App\Models\Parametre::get('entreprise_nom', 'BioMédical Inventaire SARL'); @endphp
    <div class="bul-header p-4">
        <div class="row align-items-center">
            <div class="col-8">
                <h4 class="mb-0 fw-bold">{{ App\Models\Parametre::get('entreprise_nom', 'BioMédical Inventaire SARL') }}</h4>
                <p class="mb-0 opacity-75 small">{{ App\Models\Parametre::get('entreprise_adresse', '') }} — {{ App\Models\Parametre::get('entreprise_ville', 'Yaoundé') }}, Cameroun</p>
                <p class="mb-0 opacity-75 small">NIU : {{ App\Models\Parametre::get('entreprise_niu', '—') }} | RC : {{ App\Models\Parametre::get('entreprise_rc', '—') }}</p>
            </div>
            <div class="col-4 text-end">
                <div class="fw-bold fs-5">BULLETIN DE PAIE</div>
                <div class="opacity-75">{{ strtoupper($bulletin->mois_nom) }} {{ $bulletin->annee }}</div>
                <div class="small opacity-75">N° {{ $bulletin->numero }}</div>
            </div>
        </div>
    </div>

    <div class="p-4">

        {{-- Informations employé --}}
        <div class="row mb-4 p-3 bg-light rounded">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0 small">
                    <tr><th style="width:160px">Nom & Prénom</th><td class="fw-bold">{{ $bulletin->employe->nom_complet }}</td></tr>
                    <tr><th>Matricule</th><td>{{ $bulletin->employe->matricule }}</td></tr>
                    <tr><th>Poste</th><td>{{ $bulletin->employe->poste }}</td></tr>
                    <tr><th>Service</th><td>{{ $bulletin->employe->service?->nom ?? '—' }}</td></tr>
                    <tr><th>N° CNPS</th><td>{{ $bulletin->employe->numero_cnps ?? '—' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0 small">
                    <tr><th style="width:160px">Type de contrat</th><td>{{ $bulletin->employe->contrat_label }}</td></tr>
                    <tr><th>Date d'embauche</th><td>{{ $bulletin->employe->date_embauche->format('d/m/Y') }}</td></tr>
                    <tr><th>Situation</th><td>{{ $bulletin->employe->situation_label }}, {{ $bulletin->employe->nombre_enfants }} enfant(s)</td></tr>
                    <tr><th>Période</th><td>Du {{ $bulletin->periode_debut->format('d/m/Y') }} au {{ $bulletin->periode_fin->format('d/m/Y') }}</td></tr>
                    <tr><th>Jours travaillés</th><td>{{ $bulletin->jours_travailles }} jours</td></tr>
                </table>
            </div>
        </div>

        {{-- Tableau des gains --}}
        <h6 class="fw-bold text-uppercase text-muted mb-2">Éléments de rémunération (Gains)</h6>
        <table class="table table-bordered table-sm mb-4">
            <thead>
                <tr class="bul-table">
                    <th>Désignation</th>
                    <th class="text-end" style="width:200px">Montant (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Salaire de base</td>
                    <td class="text-end">{{ number_format($bulletin->salaire_base, 0, ',', ' ') }}</td>
                </tr>

                @if(!empty($bulletin->details_primes))
                @foreach($bulletin->details_primes as $prime)
                @if(!empty($prime['montant']) && $prime['montant'] > 0)
                <tr>
                    <td class="ps-4 text-muted">{{ $prime['type'] ?? 'Prime' }}</td>
                    <td class="text-end">{{ number_format($prime['montant'], 0, ',', ' ') }}</td>
                </tr>
                @endif
                @endforeach
                @endif

                @if(!empty($bulletin->details_indemnites))
                @foreach($bulletin->details_indemnites as $indem)
                @if(!empty($indem['montant']) && $indem['montant'] > 0)
                <tr>
                    <td class="ps-4 text-muted">{{ $indem['type'] ?? 'Indemnité' }}</td>
                    <td class="text-end">{{ number_format($indem['montant'], 0, ',', ' ') }}</td>
                </tr>
                @endif
                @endforeach
                @endif

                @if($bulletin->avantages_nature > 0)
                <tr>
                    <td class="ps-4 text-muted">Avantages en nature</td>
                    <td class="text-end">{{ number_format($bulletin->avantages_nature, 0, ',', ' ') }}</td>
                </tr>
                @endif

                <tr class="table-success fw-bold">
                    <td>TOTAL BRUT</td>
                    <td class="text-end">{{ number_format($bulletin->salaire_brut, 0, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Tableau des retenues --}}
        <h6 class="fw-bold text-uppercase text-muted mb-2">Cotisations et Retenues</h6>
        <table class="table table-bordered table-sm mb-4">
            <thead>
                <tr class="bul-table">
                    <th>Désignation</th>
                    <th class="text-center" style="width:120px">Base (FCFA)</th>
                    <th class="text-center" style="width:80px">Taux</th>
                    <th class="text-end" style="width:130px">Montant (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>CNPS salarié</strong>
                        <small class="text-muted d-block">Vieillesse / Invalidité / Décès — plafond 750 000 FCFA</small>
                    </td>
                    <td class="text-center">{{ number_format(min($bulletin->salaire_brut, 750000), 0, ',', ' ') }}</td>
                    <td class="text-center">4,20 %</td>
                    <td class="text-end text-danger fw-semibold">{{ number_format($bulletin->cotisation_cnps_salarie, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>
                        <strong>IRPP</strong>
                        <small class="text-muted d-block">Impôt sur le Revenu des Personnes Physiques — barème progressif</small>
                    </td>
                    <td class="text-center">—</td>
                    <td class="text-center">Barème</td>
                    <td class="text-end text-danger fw-semibold">{{ number_format($bulletin->irpp, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>
                        <strong>CAC</strong>
                        <small class="text-muted d-block">Centimes Additionnels Communaux</small>
                    </td>
                    <td class="text-center">{{ number_format($bulletin->irpp, 0, ',', ' ') }}</td>
                    <td class="text-center">10,00 %</td>
                    <td class="text-end text-danger fw-semibold">{{ number_format($bulletin->cac, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>
                        <strong>RAV</strong>
                        <small class="text-muted d-block">Redevance Audiovisuelle</small>
                    </td>
                    <td class="text-center">Forfait</td>
                    <td class="text-center">—</td>
                    <td class="text-end text-danger fw-semibold">{{ number_format($bulletin->rav, 0, ',', ' ') }}</td>
                </tr>
                @if($bulletin->avances_deduites > 0)
                <tr>
                    <td><strong>Avances sur salaire déduites</strong></td>
                    <td class="text-center">—</td>
                    <td class="text-center">—</td>
                    <td class="text-end text-danger fw-semibold">{{ number_format($bulletin->avances_deduites, 0, ',', ' ') }}</td>
                </tr>
                @endif
                <tr class="table-danger fw-bold">
                    <td colspan="3">TOTAL RETENUES</td>
                    <td class="text-end">{{ number_format($bulletin->total_retenues, 0, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Net à payer --}}
        <div class="row">
            <div class="col-md-6 offset-md-6">
                <table class="table table-bordered table-sm">
                    <tr>
                        <td class="fw-semibold">Salaire brut</td>
                        <td class="text-end">{{ number_format($bulletin->salaire_brut, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Total retenues</td>
                        <td class="text-end text-danger">– {{ number_format($bulletin->total_retenues, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    <tr class="bul-net">
                        <td class="fw-bold">NET À PAYER</td>
                        <td class="text-end fw-bold">{{ number_format($bulletin->net_a_payer, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Charge employeur --}}
        <div class="bg-light rounded p-3 mb-4">
            <h6 class="text-muted small text-uppercase mb-2">Informations employeur (non déduites du salarié)</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between small">
                        <span>CNPS employeur (~12,95 %)</span>
                        <strong>{{ number_format($bulletin->cotisation_cnps_employeur, 0, ',', ' ') }} FCFA</strong>
                    </div>
                    <small class="text-muted">Vieillesse (4,2 %) + Allocations Familiales (7 %) + Accidents Travail (1,75 %)</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="small">
                        Coût total employeur :
                        <strong>{{ number_format($bulletin->salaire_brut + $bulletin->cotisation_cnps_employeur, 0, ',', ' ') }} FCFA</strong>
                    </div>
                </div>
            </div>
        </div>

        @if($bulletin->date_paiement)
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            Payé le {{ $bulletin->date_paiement->format('d/m/Y') }}
            par {{ match($bulletin->mode_paiement) { 'virement'=>'virement bancaire', 'especes'=>'espèces', 'cheque'=>'chèque', 'mobile_money'=>'Mobile Money', default=>$bulletin->mode_paiement } }}
        </div>
        @endif

        {{-- Signatures --}}
        <div class="row mt-5 pt-4">
            <div class="col-4 text-center">
                <div class="border-top pt-2 mt-5">
                    <small class="text-muted">Signature de l'employé</small>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top pt-2 mt-5">
                    <small class="text-muted">Cachet & Signature DRH</small>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top pt-2 mt-5">
                    <small class="text-muted">Cachet Directeur Général</small>
                </div>
            </div>
        </div>

        <p class="text-muted small text-center mt-4 border-top pt-3">
            Ce bulletin de paie est établi conformément au Code du Travail du Cameroun (Loi n°92/007) et à la réglementation fiscale en vigueur.
            À conserver sans limitation de durée.
        </p>
    </div>
</div>

{{-- Modal paiement --}}
@can('rh.bulletins.payer')
<div class="modal fade" id="modalPaiement" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Enregistrer le paiement</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rh.bulletins.payer', $bulletin) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mode de paiement</label>
                        <select name="mode_paiement" class="form-select" required>
                            <option value="virement">Virement bancaire</option>
                            <option value="especes">Espèces</option>
                            <option value="cheque">Chèque</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de paiement</label>
                        <input type="date" name="date_paiement" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="bg-success text-white rounded p-2 text-center fw-bold">
                        {{ number_format($bulletin->net_a_payer, 0, ',', ' ') }} FCFA
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success w-100"><i class="bi bi-cash me-1"></i>Confirmer le paiement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
