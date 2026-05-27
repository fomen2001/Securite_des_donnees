@extends('layouts.app')

@section('title', 'Maintenances')
@section('page-title', 'Gestion des maintenances')

@section('content')

{{-- Stats rapides --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-info shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-info">{{ $stats['planifiees'] }}</div>
            <div class="small text-muted">Planifiées</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-warning shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-warning">{{ $stats['en_cours'] }}</div>
            <div class="small text-muted">En cours</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-success">{{ $stats['terminees'] }}</div>
            <div class="small text-muted">Terminées cette année</div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="planifiee" {{ request('statut') === 'planifiee' ? 'selected' : '' }}>Planifiées</option>
                    <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                    <option value="terminee" {{ request('statut') === 'terminee' ? 'selected' : '' }}>Terminées</option>
                    <option value="annulee" {{ request('statut') === 'annulee' ? 'selected' : '' }}>Annulées</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous types</option>
                    <option value="preventive" {{ request('type') === 'preventive' ? 'selected' : '' }}>Préventive</option>
                    <option value="corrective" {{ request('type') === 'corrective' ? 'selected' : '' }}>Corrective</option>
                    <option value="calibration" {{ request('type') === 'calibration' ? 'selected' : '' }}>Calibration</option>
                    <option value="verification" {{ request('type') === 'verification' ? 'selected' : '' }}>Vérification</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('maintenances.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
            <div class="col-md-5 text-end">
                <a href="{{ route('maintenances.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Nouvelle maintenance
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Équipement</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Date planifiée</th>
                    <th>Technicien</th>
                    <th>Coût</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenances as $m)
                <tr>
                    <td>
                        <a href="{{ route('equipements.show', $m->equipement) }}" class="text-decoration-none fw-medium">
                            {{ Str::limit($m->equipement->designation, 35) }}
                        </a>
                    </td>
                    <td class="small">{{ $m->type_label }}</td>
                    <td><span class="badge bg-{{ $m->statut_badge }}">{{ ucfirst($m->statut) }}</span></td>
                    <td class="small {{ $m->statut === 'planifiee' && $m->date_planifiee->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                        {{ $m->date_planifiee->format('d/m/Y') }}
                    </td>
                    <td class="small text-muted">{{ $m->technicien ?? '—' }}</td>
                    <td class="small">{{ $m->cout ? number_format($m->cout, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('maintenances.show', $m) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('maintenances.edit', $m) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>Aucune maintenance enregistrée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $maintenances->links() }}</div>
@endsection
