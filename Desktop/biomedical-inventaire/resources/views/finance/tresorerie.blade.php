@extends('layouts.app')

@section('title', 'Tableau de trésorerie')
@section('page-title', 'Trésorerie')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item active">Trésorerie</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex gap-2">
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

{{-- Solde final --}}
@php $soldeFinal = $flux->last()['solde'] ?? 0; @endphp
<div class="alert {{ $soldeFinal >= 0 ? 'alert-success' : 'alert-danger' }} mb-4">
    <div class="d-flex align-items-center gap-3">
        <i class="bi bi-cash-coin fs-3"></i>
        <div>
            <strong>Solde de trésorerie cumulé {{ $annee }} :</strong>
            <span class="fs-5 fw-bold ms-2">{{ $soldeFinal >= 0 ? '+' : '' }}{{ number_format($soldeFinal, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>
</div>

{{-- Tableau mensuel --}}
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-table me-2"></i>Flux mensuels de trésorerie</div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Mois</th>
                    <th class="text-end text-success">Entrées</th>
                    <th class="text-end text-danger">Sorties</th>
                    <th class="text-end">Flux net</th>
                    <th class="text-end">Solde cumulé</th>
                </tr>
            </thead>
            <tbody>
                @foreach($flux as $f)
                <tr>
                    <td class="fw-semibold">{{ $f['mois'] }}</td>
                    <td class="text-end text-success fw-semibold">{{ number_format($f['entrees'], 0, ',', ' ') }}</td>
                    <td class="text-end text-danger">{{ number_format($f['sorties'], 0, ',', ' ') }}</td>
                    <td class="text-end fw-semibold {{ $f['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $f['net'] >= 0 ? '+' : '' }}{{ number_format($f['net'], 0, ',', ' ') }}
                    </td>
                    <td class="text-end fw-bold {{ $f['solde'] >= 0 ? 'text-primary' : 'text-danger' }}">
                        {{ number_format($f['solde'], 0, ',', ' ') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-dark fw-bold">
                <tr>
                    <td>TOTAL</td>
                    <td class="text-end text-success">{{ number_format($flux->sum('entrees'), 0, ',', ' ') }}</td>
                    <td class="text-end text-danger">{{ number_format($flux->sum('sorties'), 0, ',', ' ') }}</td>
                    <td class="text-end {{ $flux->sum('net') >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($flux->sum('net'), 0, ',', ' ') }}
                    </td>
                    <td class="text-end">{{ number_format($soldeFinal, 0, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Entrées = ventes payées | Sorties = dépenses payées + bulletins de paie payés
    </div>
</div>

{{-- Graphe --}}
<div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-graph-up me-2"></i>Évolution de la trésorerie</div>
    <div class="card-body"><canvas id="chartTresorerie" height="80"></canvas></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const flux = @json($flux->values());
new Chart(document.getElementById('chartTresorerie'), {
    data: {
        labels: flux.map(f => f.mois),
        datasets: [
            { type: 'bar', label: 'Entrées', data: flux.map(f => f.entrees), backgroundColor: '#19875450', borderColor: '#198754', borderWidth: 1 },
            { type: 'bar', label: 'Sorties', data: flux.map(f => -f.sorties), backgroundColor: '#dc354550', borderColor: '#dc3545', borderWidth: 1 },
            { type: 'line', label: 'Solde cumulé', data: flux.map(f => f.solde), borderColor: '#0d6efd', borderWidth: 2, tension: 0.4, pointRadius: 5, fill: false, backgroundColor: 'transparent' },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { ticks: { callback: v => (v/1000).toFixed(0)+'k' } } }
    }
});
</script>
@endpush
