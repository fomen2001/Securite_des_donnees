@extends('layouts.app')

@section('title', 'Finance — Tableau de bord')
@section('page-title', 'Tableau de bord Finance')
@section('breadcrumb')
    <li class="breadcrumb-item active">Finance</li>
@endsection

@push('styles')
<style>
.kpi-card { border-left: 4px solid; border-radius: 8px; }
.kpi-ca      { border-color: #198754; }
.kpi-dep     { border-color: #dc3545; }
.kpi-sal     { border-color: #0d6efd; }
.kpi-res     { border-color: #6f42c1; }
.kpi-res.negative { border-color: #dc3545; }
</style>
@endpush

@section('content')

{{-- KPIs mois courant --}}
<div class="row g-3 mb-4">
    @php $moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre']; @endphp
    <div class="col-12 mb-1">
        <small class="text-muted fw-semibold text-uppercase">Mois courant — {{ $moisNoms[$mois] }} {{ $annee }}</small>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card kpi-ca p-3">
            <div class="text-muted small">Chiffre d'affaires</div>
            <div class="fw-bold fs-4 text-success">{{ number_format($ca_mois, 0, ',', ' ') }}</div>
            <div class="text-muted small">FCFA (ventes encaissées)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card kpi-dep p-3">
            <div class="text-muted small">Dépenses</div>
            <div class="fw-bold fs-4 text-danger">{{ number_format($depenses_mois, 0, ',', ' ') }}</div>
            <div class="text-muted small">FCFA (approuvées)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card kpi-sal p-3">
            <div class="text-muted small">Masse salariale</div>
            <div class="fw-bold fs-4 text-primary">{{ number_format($masse_salariale_mois, 0, ',', ' ') }}</div>
            <div class="text-muted small">FCFA (nets à payer)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card {{ $resultat_mois >= 0 ? 'kpi-res' : 'kpi-res negative' }} p-3">
            <div class="text-muted small">Résultat net</div>
            <div class="fw-bold fs-4 {{ $resultat_mois >= 0 ? 'text-purple' : 'text-danger' }}" style="color:{{ $resultat_mois >= 0 ? '#6f42c1' : '#dc3545' }}">
                {{ $resultat_mois >= 0 ? '+' : '' }}{{ number_format($resultat_mois, 0, ',', ' ') }}
            </div>
            <div class="text-muted small">FCFA (CA − charges)</div>
        </div>
    </div>
</div>

{{-- KPIs annuels --}}
<div class="row g-3 mb-4">
    <div class="col-12 mb-1">
        <small class="text-muted fw-semibold text-uppercase">Cumul annuel {{ $annee }}</small>
    </div>
    @foreach([
        ['CA total', $ca_annuel, 'success', 'bi-graph-up-arrow'],
        ['Dépenses', $depenses_annuel, 'danger', 'bi-wallet2'],
        ['Masse salariale', $masse_salariale_annuelle, 'primary', 'bi-people'],
        ['Résultat net', $resultat_annuel, $resultat_annuel >= 0 ? 'success' : 'danger', 'bi-calculator'],
    ] as [$label, $val, $color, $icon])
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-{{ $color }} bg-opacity-10 p-3">
                    <i class="bi {{ $icon }} text-{{ $color }} fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="fw-bold text-{{ $color }}">{{ number_format($val, 0, ',', ' ') }} FCFA</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4">

    {{-- Graphe évolution --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-bar-chart me-2"></i>Évolution mensuelle (12 derniers mois)
            </div>
            <div class="card-body">
                <canvas id="chartEvolution" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Dépenses par catégorie --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-pie-chart me-2"></i>Dépenses par catégorie ({{ $annee }})
            </div>
            <div class="card-body">
                @forelse($depenses_par_cat as $cat)
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>
                            <span class="badge" style="background:{{ $cat->couleur }}">{{ $cat->nom }}</span>
                        </span>
                        <span class="fw-semibold">{{ number_format($cat->total, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @php
                        $total_dep = $depenses_par_cat->sum('total');
                        $pct = $total_dep > 0 ? ($cat->total / $total_dep) * 100 : 0;
                    @endphp
                    <div class="progress" style="height:6px">
                        <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $cat->couleur }}"></div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">Aucune dépense cette année</p>
                @endforelse
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('finance.depenses.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
        </div>
    </div>

    {{-- Accès rapides --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-lightning me-2"></i>Actions rapides</div>
            <div class="list-group list-group-flush">
                @can('finance.depenses.creer')
                <a href="{{ route('finance.depenses.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle text-danger"></i> Enregistrer une dépense
                </a>
                @endcan
                <a href="{{ route('finance.rapports.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-bar-graph text-primary"></i> Rapport P&L
                </a>
                <a href="{{ route('finance.tresorerie') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-cash-coin text-success"></i> Tableau de trésorerie
                </a>
                <a href="{{ route('finance.depenses.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-list-ul text-secondary"></i> Toutes les dépenses
                </a>
            </div>
        </div>
    </div>

    {{-- Dernières dépenses --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-wallet2 me-2"></i>Dernières dépenses</span>
                @can('finance.depenses.creer')
                <a href="{{ route('finance.depenses.create') }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-plus me-1"></i>Nouvelle
                </a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Libellé</th><th>Catégorie</th><th class="text-end">Montant</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @forelse($dernieresDepenses as $d)
                        <tr>
                            <td class="small">{{ $d->date_depense->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('finance.depenses.show', $d) }}" class="text-decoration-none">{{ Str::limit($d->libelle, 35) }}</a>
                            </td>
                            <td>
                                @if($d->categorie)
                                <span class="badge" style="background:{{ $d->categorie->couleur }}">{{ $d->categorie->nom }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($d->montant_ttc, 0, ',', ' ') }}</td>
                            <td><span class="badge bg-{{ $d->statut_badge }}">{{ $d->statut_label }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucune dépense</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const evolution = @json($evolution);
const labels  = evolution.map(e => e.mois);
const ca      = evolution.map(e => e.ca);
const charges = evolution.map(e => e.depenses);
const result  = evolution.map(e => e.resultat);

new Chart(document.getElementById('chartEvolution'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'CA (FCFA)', data: ca, backgroundColor: '#19875440', borderColor: '#198754', borderWidth: 2 },
            { label: 'Charges', data: charges, backgroundColor: '#dc354540', borderColor: '#dc3545', borderWidth: 2 },
            { label: 'Résultat', data: result, type: 'line', borderColor: '#6f42c1', backgroundColor: 'transparent', borderWidth: 2, tension: 0.4, pointRadius: 4 },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { ticks: { callback: v => (v/1000).toFixed(0) + 'k' } } }
    }
});
</script>
@endpush
