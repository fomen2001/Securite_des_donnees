@extends('layouts.app')

@section('title', 'Rapport P&L ' . $annee)
@section('page-title', 'Rapport Financier — Compte de Résultat')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item active">Rapport P&L</li>
@endsection

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .sidebar, .topbar { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .p-4 { padding: 0 !important; }
}
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <form method="GET" class="d-flex gap-2 align-items-center">
        <select name="annee" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            @foreach($annees as $a)
                <option value="{{ $a }}" @selected($annee == $a)>{{ $a }}</option>
            @endforeach
        </select>
    </form>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i>Imprimer
    </button>
</div>

{{-- En-tête rapport --}}
<div class="text-center mb-4">
    <h4 class="fw-bold">COMPTE DE RÉSULTAT</h4>
    <p class="text-muted">Exercice {{ $annee }} — {{ App\Models\Parametre::get('entreprise_nom', 'BioMédical Inventaire SARL') }}</p>
</div>

{{-- Tableau mensuel --}}
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-table me-2"></i>Évolution mensuelle</div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Mois</th>
                    <th class="text-end text-success">Recettes</th>
                    <th class="text-end text-warning">Masse salariale</th>
                    <th class="text-end text-danger">Dépenses</th>
                    <th class="text-end text-info">Charges totales</th>
                    <th class="text-end">Résultat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rapport as $r)
                <tr class="{{ $r['resultat'] < 0 ? 'table-danger' : '' }}">
                    <td class="fw-semibold">{{ $moisLabels[$r['mois']] }}</td>
                    <td class="text-end text-success fw-semibold">{{ number_format($r['recettes'], 0, ',', ' ') }}</td>
                    <td class="text-end text-warning">{{ number_format($r['masse_salariale'], 0, ',', ' ') }}</td>
                    <td class="text-end text-danger">{{ number_format($r['depenses'], 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($r['charges_totales'], 0, ',', ' ') }}</td>
                    <td class="text-end fw-bold {{ $r['resultat'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $r['resultat'] >= 0 ? '+' : '' }}{{ number_format($r['resultat'], 0, ',', ' ') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-dark fw-bold">
                <tr>
                    <td>TOTAL {{ $annee }}</td>
                    <td class="text-end text-success">{{ number_format($totaux['recettes'], 0, ',', ' ') }}</td>
                    <td class="text-end text-warning">{{ number_format($totaux['masse_salariale'], 0, ',', ' ') }}</td>
                    <td class="text-end text-danger">{{ number_format($totaux['depenses'], 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($totaux['charges_totales'], 0, ',', ' ') }}</td>
                    <td class="text-end {{ $totaux['resultat'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $totaux['resultat'] >= 0 ? '+' : '' }}{{ number_format($totaux['resultat'], 0, ',', ' ') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="row g-4">

    {{-- Résumé P&L --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-list-check me-2"></i>Résumé P&L {{ $annee }}</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr class="table-success"><td class="fw-bold ps-3">PRODUITS</td><td></td></tr>
                        <tr><td class="ps-4">Chiffre d'affaires</td><td class="text-end pe-3 fw-semibold text-success">+ {{ number_format($totaux['recettes'], 0, ',', ' ') }}</td></tr>

                        <tr class="table-danger"><td class="fw-bold ps-3">CHARGES</td><td></td></tr>
                        <tr><td class="ps-4">Masse salariale</td><td class="text-end pe-3 text-danger">– {{ number_format($totaux['masse_salariale'], 0, ',', ' ') }}</td></tr>
                        <tr><td class="ps-4">Dépenses d'exploitation</td><td class="text-end pe-3 text-danger">– {{ number_format($totaux['depenses'], 0, ',', ' ') }}</td></tr>
                        <tr class="table-light fw-semibold"><td class="ps-3">TOTAL CHARGES</td><td class="text-end pe-3">{{ number_format($totaux['charges_totales'], 0, ',', ' ') }}</td></tr>

                        @php $taux = $totaux['recettes'] > 0 ? ($totaux['resultat'] / $totaux['recettes']) * 100 : 0; @endphp
                        <tr class="{{ $totaux['resultat'] >= 0 ? 'bg-success' : 'bg-danger' }} text-white fw-bold">
                            <td class="ps-3">RÉSULTAT NET</td>
                            <td class="text-end pe-3">
                                {{ $totaux['resultat'] >= 0 ? '+' : '' }}{{ number_format($totaux['resultat'], 0, ',', ' ') }}
                            </td>
                        </tr>
                        <tr class="table-light">
                            <td class="ps-3 small text-muted">Taux de marge nette</td>
                            <td class="text-end pe-3 small fw-semibold">{{ number_format($taux, 1) }} %</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Dépenses par catégorie --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-pie-chart me-2"></i>Détail des dépenses par catégorie</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Catégorie</th><th>Type</th><th class="text-end">Montant</th><th class="text-end">%</th><th class="text-center">Nb</th></tr>
                    </thead>
                    <tbody>
                        @forelse($depParCat as $cat)
                        <tr>
                            <td>
                                <span class="badge me-1" style="background:{{ $cat->couleur }}"> </span>
                                {{ $cat->nom }}
                            </td>
                            <td><small class="text-muted">{{ $cat->type }}</small></td>
                            <td class="text-end fw-semibold">{{ number_format($cat->total, 0, ',', ' ') }}</td>
                            <td class="text-end">
                                @php $pct = $totaux['depenses'] > 0 ? ($cat->total / $totaux['depenses']) * 100 : 0; @endphp
                                {{ number_format($pct, 1) }} %
                            </td>
                            <td class="text-center">{{ $cat->nb }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucune dépense</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top clients --}}
        @if($topClients->count())
        <div class="card mt-4">
            <div class="card-header fw-semibold"><i class="bi bi-trophy me-2"></i>Top 5 clients (recettes)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Client</th><th class="text-end">CA</th><th class="text-center">Factures</th></tr></thead>
                    <tbody>
                        @foreach($topClients as $client)
                        <tr>
                            <td>{{ $client->nom }}</td>
                            <td class="text-end fw-semibold text-success">{{ number_format($client->total, 0, ',', ' ') }}</td>
                            <td class="text-center">{{ $client->nb }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

</div>

{{-- Graphe --}}
<div class="card mt-4 no-print">
    <div class="card-header fw-semibold"><i class="bi bi-bar-chart me-2"></i>Évolution graphique {{ $annee }}</div>
    <div class="card-body"><canvas id="chartRapport" height="80"></canvas></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const rapport = @json($rapport);
new Chart(document.getElementById('chartRapport'), {
    type: 'bar',
    data: {
        labels: rapport.map(r => r.mois_label),
        datasets: [
            { label: 'Recettes', data: rapport.map(r => r.recettes), backgroundColor: '#19875480', borderColor: '#198754', borderWidth: 1 },
            { label: 'Charges', data: rapport.map(r => r.charges_totales), backgroundColor: '#dc354580', borderColor: '#dc3545', borderWidth: 1 },
            { label: 'Résultat', data: rapport.map(r => r.resultat), type: 'line', borderColor: '#6f42c1', borderWidth: 2, tension: 0.4, pointRadius: 5, backgroundColor: 'transparent' },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { ticks: { callback: v => (v/1000).toFixed(0)+'k FCFA' } } }
    }
});
</script>
@endpush
