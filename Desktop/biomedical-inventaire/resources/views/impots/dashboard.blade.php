@extends('layouts.app')

@section('title', 'Tableau de bord Fiscal')
@section('page-title', 'Gestion Fiscale — SARL')
@section('breadcrumb')
    <li class="breadcrumb-item active">Fiscalité</li>
@endsection

@section('content')

{{-- KPIs ─────────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center p-3 border-primary">
            <div class="fw-bold fs-3 text-primary">{{ number_format($tvaTotaleAnnee, 0, ',', ' ') }}</div>
            <small class="text-muted">TVA payée {{ $annee }} (FCFA)</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 border-warning">
            <div class="fw-bold fs-3 text-warning">{{ number_format($isTotalAnnee, 0, ',', ' ') }}</div>
            <small class="text-muted">IS payé {{ $annee }} (FCFA)</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 {{ $patente?->statut === 'payee' ? 'border-success' : 'border-danger' }}">
            <div class="fw-bold fs-3 {{ $patente?->statut === 'payee' ? 'text-success' : 'text-danger' }}">
                @if($patente)
                    {{ number_format($patente->montant_total, 0, ',', ' ') }}
                @else —
                @endif
            </div>
            <small class="text-muted">Patente {{ $annee }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 {{ $tauxConformite >= 80 ? 'border-success' : ($tauxConformite >= 50 ? 'border-warning' : 'border-danger') }}">
            <div class="fw-bold fs-3 {{ $tauxConformite >= 80 ? 'text-success' : ($tauxConformite >= 50 ? 'text-warning' : 'text-danger') }}">
                {{ $tauxConformite }} %
            </div>
            <small class="text-muted">Taux de conformité fiscale</small>
        </div>
    </div>
</div>

<div class="row g-4">
{{-- Calendrier fiscal ──────────────────────────────────────────────────── --}}
<div class="col-lg-5">
<div class="card h-100">
    <div class="card-header fw-semibold"><i class="bi bi-calendar-event me-2"></i>Prochaines échéances fiscales</div>
    <div class="card-body p-0">
        @forelse($echeances as $e)
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
            <div class="text-center" style="min-width:50px">
                <div class="fw-bold text-{{ $e['couleur'] }}">{{ $e['date']->format('d') }}</div>
                <small class="text-muted">{{ $e['date']->format('M') }}</small>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold small">{{ $e['label'] }}</div>
                <span class="badge bg-{{
                    match($e['statut']) {
                        'payee' => 'success', 'soumise' => 'primary',
                        'en_retard' => 'danger', 'non_cree' => 'light text-dark',
                        default => 'secondary'
                    }
                }} small">
                    {{ match($e['statut']) {
                        'payee' => 'Payée', 'soumise' => 'Soumise',
                        'en_retard' => 'En retard', 'non_cree' => 'Non créée',
                        default => ucfirst($e['statut'])
                    } }}
                </span>
            </div>
            <div>
                @php $jours = now()->diffInDays($e['date'], false); @endphp
                <span class="badge {{ $jours < 7 ? 'bg-danger' : ($jours < 21 ? 'bg-warning text-dark' : 'bg-light text-dark') }}">
                    {{ $jours >= 0 ? 'J-'.$jours : 'J+'.(abs($jours)) }}
                </span>
            </div>
            <a href="{{ $e['lien'] }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
            Aucune échéance dans les 90 prochains jours.
        </div>
        @endforelse
    </div>
</div>
</div>

{{-- Suivi TVA ──────────────────────────────────────────────────────────── --}}
<div class="col-lg-7">
<div class="card mb-4">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-receipt me-2"></i>TVA {{ $annee }}</span>
        <a href="{{ route('impots.tva.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle déclaration</a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    @foreach(['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'] as $i => $m)
                    <th class="text-center small">{{ $m }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @for($m = 1; $m <= 12; $m++)
                    @php $d = $tvaAnnee->get($m); @endphp
                    <td class="text-center p-1">
                        @if($d)
                            <a href="{{ route('impots.tva.show', $d) }}" class="badge bg-{{ $d->statut_badge }} text-decoration-none" style="font-size:.65rem">
                                {{ $d->statut_label }}
                            </a>
                        @elseif($m < now()->month || ($m == now()->month && now()->day > 15))
                            <span class="badge bg-danger" style="font-size:.65rem">Manquante</span>
                        @else
                            <span class="text-muted" style="font-size:.75rem">—</span>
                        @endif
                    </td>
                    @endfor
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- IS Acomptes ──────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bank me-2"></i>IS {{ $annee }} — Acomptes provisionnels</span>
        <a href="{{ route('impots.is.create') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach([1=>'15 Fév',2=>'15 Mai',3=>'15 Août',4=>'15 Nov'] as $t => $date)
            @php $a = $acomptesIS->get($t); @endphp
            <div class="col-6 col-md-3">
                <div class="card border text-center p-2 {{ $a?->statut === 'payee' ? 'border-success' : ($a?->statut === 'en_retard' ? 'border-danger' : '') }}">
                    <div class="fw-bold">T{{ $t }}</div>
                    <small class="text-muted d-block">{{ $date }}</small>
                    @if($a)
                        <div class="fw-semibold small mt-1">{{ number_format($a->montant_acompte, 0, ',', ' ') }} FCFA</div>
                        <span class="badge bg-{{ $a->statut_badge }} mt-1">{{ $a->statut_label }}</span>
                    @else
                        <span class="badge bg-secondary mt-2">Non créé</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Mémo obligations SARL Cameroun ───────────────────────────────────── --}}
<div class="card border-info">
    <div class="card-header fw-semibold text-info"><i class="bi bi-info-circle me-2"></i>Obligations SARL — CGI Cameroun</div>
    <div class="card-body small">
        <div class="row g-2">
            <div class="col-md-6">
                <strong>TVA (19,25 %)</strong>
                <ul class="mb-0 ps-3">
                    <li>Déclaration mensuelle avant le 15 du mois suivant</li>
                    <li>Crédit reportable si TVA déductible &gt; collectée</li>
                </ul>
            </div>
            <div class="col-md-6">
                <strong>IS (30 % / min. 1 % CA)</strong>
                <ul class="mb-0 ps-3">
                    <li>4 acomptes : 15 Fév, 15 Mai, 15 Août, 15 Nov</li>
                    <li>Déclaration annuelle (DSF) avant le 15 Avril N+1</li>
                </ul>
            </div>
            <div class="col-md-6">
                <strong>Patente</strong>
                <ul class="mb-0 ps-3">
                    <li>Renouvellement annuel avant le 31 Mars</li>
                    <li>Droit fixe + 0,159 % CA + 10 % CAC</li>
                </ul>
            </div>
            <div class="col-md-6">
                <strong>CNPS (déjà dans RH)</strong>
                <ul class="mb-0 ps-3">
                    <li>Salarié : 4,2 % | Employeur : 12,95 % (plaf. 750 000)</li>
                    <li>RAV : 2 500 FCFA/mois</li>
                </ul>
            </div>
        </div>
    </div>
</div>

</div>{{-- col --}}
</div>{{-- row --}}
@endsection
