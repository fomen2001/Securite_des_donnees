@extends('layouts.app')

@section('title', 'Mouvements de stock')
@section('page-title', 'Journal des mouvements de stock')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $mouvements->total() }} mouvement(s)</span>
    <a href="{{ route('mouvements.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nouveau mouvement
    </a>
</div>

{{-- Filtres rapides --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous types</option>
                    <option value="entree" {{ request('type') === 'entree' ? 'selected' : '' }}>Entrées</option>
                    <option value="sortie" {{ request('type') === 'sortie' ? 'selected' : '' }}>Sorties</option>
                    <option value="transfert" {{ request('type') === 'transfert' ? 'selected' : '' }}>Transferts</option>
                    <option value="ajustement" {{ request('type') === 'ajustement' ? 'selected' : '' }}>Ajustements</option>
                    <option value="reforme" {{ request('type') === 'reforme' ? 'selected' : '' }}>Réformes</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-1">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('mouvements.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Équipement</th>
                    <th>Type</th>
                    <th class="text-center">Qté</th>
                    <th class="text-center">Avant</th>
                    <th class="text-center">Après</th>
                    <th>Service src → dest</th>
                    <th>Motif</th>
                    <th>Opérateur</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mouvements as $mv)
                <tr>
                    <td class="small text-muted">{{ $mv->date_mouvement->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('equipements.show', $mv->equipement) }}" class="text-decoration-none fw-medium">
                            {{ $mv->equipement->designation }}
                        </a>
                        <br><code class="small">{{ $mv->equipement->code_inventaire }}</code>
                    </td>
                    <td><span class="badge bg-{{ $mv->type_badge }}">{{ $mv->type_label }}</span></td>
                    <td class="text-center fw-bold">{{ $mv->quantite }}</td>
                    <td class="text-center text-muted">{{ $mv->quantite_avant }}</td>
                    <td class="text-center fw-semibold">{{ $mv->quantite_apres }}</td>
                    <td class="small text-muted">
                        {{ $mv->serviceSource?->nom ?? '—' }}
                        @if($mv->serviceDestination) → {{ $mv->serviceDestination->nom }} @endif
                    </td>
                    <td class="small text-muted">{{ Str::limit($mv->motif, 35) }}</td>
                    <td class="small text-muted">{{ $mv->user->name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>Aucun mouvement enregistré
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $mouvements->links() }}</div>
@endsection
