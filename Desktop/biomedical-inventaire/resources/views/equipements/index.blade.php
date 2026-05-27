@extends('layouts.app')

@section('title', 'Inventaire')
@section('page-title', 'Inventaire des équipements')

@section('content')

{{-- Filtres --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('equipements.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="recherche" class="form-control form-control-sm"
                    placeholder="Code, désignation, marque, N° série..."
                    value="{{ request('recherche') }}">
            </div>
            <div class="col-md-2">
                <select name="categorie_id" class="form-select form-select-sm">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $id => $nom)
                        <option value="{{ $id }}" {{ request('categorie_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="service_id" class="form-select form-select-sm">
                    <option value="">Tous les services</option>
                    @foreach($services as $id => $nom)
                        <option value="{{ $id }}" {{ request('service_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="etat" class="form-select form-select-sm">
                    <option value="">Tous les états</option>
                    <option value="operationnel" {{ request('etat') === 'operationnel' ? 'selected' : '' }}>Opérationnel</option>
                    <option value="en_maintenance" {{ request('etat') === 'en_maintenance' ? 'selected' : '' }}>En maintenance</option>
                    <option value="hors_service" {{ request('etat') === 'hors_service' ? 'selected' : '' }}>Hors service</option>
                    <option value="reformé" {{ request('etat') === 'reformé' ? 'selected' : '' }}>Réformé</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="alerte" class="form-select form-select-sm">
                    <option value="">Toutes alertes</option>
                    <option value="stock_bas" {{ request('alerte') === 'stock_bas' ? 'selected' : '' }}>Stock bas</option>
                    <option value="maintenance_echue" {{ request('alerte') === 'maintenance_echue' ? 'selected' : '' }}>Maintenance échue</option>
                    <option value="garantie_expiree" {{ request('alerte') === 'garantie_expiree' ? 'selected' : '' }}>Garantie expirée</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('equipements.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Actions --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $equipements->total() }} équipement(s)</span>
    <div class="d-flex gap-2">
        <a href="{{ route('equipements.export') }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Exporter CSV
        </a>
        <a href="{{ route('equipements.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Ajouter
        </a>
    </div>
</div>

{{-- Tableau --}}
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Désignation</th>
                    <th>Catégorie</th>
                    <th>Service</th>
                    <th class="text-center">Qté</th>
                    <th>État</th>
                    <th>Proch. maintenance</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipements as $eq)
                <tr>
                    <td><code class="small">{{ $eq->code_inventaire }}</code></td>
                    <td>
                        <div class="fw-medium">{{ $eq->designation }}</div>
                        @if($eq->marque)
                            <small class="text-muted">{{ $eq->marque }} {{ $eq->modele }}</small>
                        @endif
                    </td>
                    <td>
                        @if($eq->categorie)
                            <span class="badge" style="background-color: {{ $eq->categorie->couleur }}">
                                {{ $eq->categorie->nom }}
                            </span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $eq->service?->nom ?? '—' }}</td>
                    <td class="text-center">
                        <span class="{{ $eq->stock_alert ? 'text-danger fw-bold' : '' }}">
                            {{ $eq->quantite }}
                        </span>
                        @if($eq->stock_alert)
                            <i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="Stock bas"></i>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $eq->etat_badge }}">{{ $eq->etat_label }}</span>
                    </td>
                    <td>
                        @if($eq->prochaine_maintenance)
                            <span class="{{ $eq->maintenance_echue ? 'text-danger fw-semibold' : 'text-muted' }} small">
                                {{ $eq->prochaine_maintenance->format('d/m/Y') }}
                                @if($eq->maintenance_echue)
                                    <i class="bi bi-alarm-fill ms-1 text-danger"></i>
                                @endif
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('equipements.show', $eq) }}" class="btn btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('equipements.edit', $eq) }}" class="btn btn-outline-secondary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('mouvements.create', ['equipement_id' => $eq->id]) }}" class="btn btn-outline-info" title="Mouvement">
                                <i class="bi bi-arrow-left-right"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Aucun équipement trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $equipements->links() }}
</div>

@endsection
