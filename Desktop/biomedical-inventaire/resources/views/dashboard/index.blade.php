@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')

{{-- Cartes statistiques --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-primary bg-opacity-10">
                    <i class="bi bi-clipboard2-pulse fs-3 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold">{{ $stats['total_equipements'] }}</div>
                    <div class="text-muted small">Total équipements</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-success bg-opacity-10">
                    <i class="bi bi-check-circle fs-3 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-success">{{ $stats['operationnels'] }}</div>
                    <div class="text-muted small">Opérationnels</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-warning bg-opacity-10">
                    <i class="bi bi-tools fs-3 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-warning">{{ $stats['en_maintenance'] }}</div>
                    <div class="text-muted small">En maintenance</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-danger bg-opacity-10">
                    <i class="bi bi-x-circle fs-3 text-danger"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-danger">{{ $stats['hors_service'] }}</div>
                    <div class="text-muted small">Hors service</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alertes --}}
@if($stats['stock_bas'] > 0 || $stats['maintenance_echue'] > 0 || $stats['garantie_expiree'] > 0)
<div class="row g-2 mb-4">
    @if($stats['stock_bas'] > 0)
    <div class="col-md-4">
        <div class="alert-banner d-flex align-items-center justify-content-between">
            <div><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                <strong>{{ $stats['stock_bas'] }}</strong> équipement(s) en stock bas</div>
            <a href="{{ route('equipements.index', ['alerte' => 'stock_bas']) }}" class="btn btn-sm btn-outline-warning">Voir</a>
        </div>
    </div>
    @endif
    @if($stats['maintenance_echue'] > 0)
    <div class="col-md-4">
        <div class="alert-banner d-flex align-items-center justify-content-between" style="border-color:#dc3545">
            <div><i class="bi bi-wrench-adjustable-circle-fill text-danger me-2"></i>
                <strong>{{ $stats['maintenance_echue'] }}</strong> maintenance(s) échue(s)</div>
            <a href="{{ route('equipements.index', ['alerte' => 'maintenance_echue']) }}" class="btn btn-sm btn-outline-danger">Voir</a>
        </div>
    </div>
    @endif
    @if($stats['garantie_expiree'] > 0)
    <div class="col-md-4">
        <div class="alert-banner d-flex align-items-center justify-content-between" style="border-color:#6c757d">
            <div><i class="bi bi-shield-x me-2"></i>
                <strong>{{ $stats['garantie_expiree'] }}</strong> garantie(s) expirée(s)</div>
            <a href="{{ route('equipements.index', ['alerte' => 'garantie_expiree']) }}" class="btn btn-sm btn-outline-secondary">Voir</a>
        </div>
    </div>
    @endif
</div>
@endif

<div class="row g-4">

    {{-- Maintenances planifiées --}}
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <strong><i class="bi bi-calendar-check me-2 text-primary"></i>Prochaines maintenances</strong>
                <a href="{{ route('maintenances.index') }}" class="btn btn-sm btn-outline-primary">Tout voir</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($maintenancesProchaines as $m)
                <a href="{{ route('maintenances.show', $m) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-medium">{{ $m->equipement->designation }}</div>
                            <small class="text-muted">{{ $m->type_label }} — {{ $m->technicien ?? 'Non assigné' }}</small>
                        </div>
                        <span class="badge bg-{{ $m->date_planifiee->diffInDays(now()) <= 7 ? 'danger' : 'info' }}">
                            {{ $m->date_planifiee->format('d/m/Y') }}
                        </span>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-muted text-center py-4">
                    <i class="bi bi-check-all fs-4"></i><br>Aucune maintenance planifiée
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Alertes stock bas --}}
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <strong><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Alertes stock</strong>
                <a href="{{ route('equipements.index', ['alerte' => 'stock_bas']) }}" class="btn btn-sm btn-outline-warning">Tout voir</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($alertesStock as $eq)
                <a href="{{ route('equipements.show', $eq) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-medium">{{ $eq->designation }}</div>
                            <small class="text-muted">{{ $eq->categorie?->nom }} — {{ $eq->service?->nom ?? 'N/A' }}</small>
                        </div>
                        <span class="badge bg-danger">{{ $eq->quantite }} / {{ $eq->quantite_min }} min</span>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-muted text-center py-4">
                    <i class="bi bi-check-all fs-4"></i><br>Tous les stocks sont suffisants
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Derniers mouvements --}}
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <strong><i class="bi bi-arrow-left-right me-2 text-info"></i>Derniers mouvements</strong>
                <a href="{{ route('mouvements.index') }}" class="btn btn-sm btn-outline-info">Tout voir</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Équipement</th>
                            <th>Type</th>
                            <th>Qté</th>
                            <th>Par</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($derniersMovements as $mv)
                        <tr>
                            <td>
                                <a href="{{ route('equipements.show', $mv->equipement) }}" class="text-decoration-none">
                                    {{ Str::limit($mv->equipement->designation, 30) }}
                                </a>
                            </td>
                            <td><span class="badge bg-{{ $mv->type_badge }}">{{ $mv->type_label }}</span></td>
                            <td>{{ $mv->quantite }}</td>
                            <td class="text-muted small">{{ $mv->user->name }}</td>
                            <td class="text-muted small">{{ $mv->date_mouvement->format('d/m H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Répartition par service --}}
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <strong><i class="bi bi-building me-2 text-secondary"></i>Par service</strong>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($equipementsParService as $svc)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="small">{{ $svc->nom }}</span>
                        <span class="badge bg-primary rounded-pill">{{ $svc->equipements_count }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
