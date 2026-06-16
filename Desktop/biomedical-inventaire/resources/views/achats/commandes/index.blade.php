@extends('layouts.app')

@section('title', 'Bons de commande')
@section('page-title', 'Bons de commande fournisseurs')
@section('breadcrumb')
    <li class="breadcrumb-item active">Achats</li>
    <li class="breadcrumb-item active">Bons de commande</li>
@endsection

@section('content')

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Commandes du mois (FCFA)</div>
                <div class="fw-bold fs-4">{{ number_format($stats['total_mois'], 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Confirmées en attente</div>
                <div class="fw-bold fs-4 text-primary">{{ $stats['en_attente'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Réception partielle</div>
                <div class="fw-bold fs-4 text-warning">{{ $stats['partielles'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total ce mois</div>
                <div class="fw-bold fs-4">{{ $stats['ce_mois'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    @foreach(['brouillon'=>'Brouillon','confirmee'=>'Confirmée','partiellement_recue'=>'Part. reçue','recue'=>'Reçue','annulee'=>'Annulée'] as $val => $label)
                        <option value="{{ $val }}" {{ request('statut') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="fournisseur_id" class="form-select form-select-sm">
                    <option value="">Tous les fournisseurs</option>
                    @foreach($fournisseurs as $id => $nom)
                        <option value="{{ $id }}" {{ request('fournisseur_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-sm btn-primary w-100">Filtrer</button>
                <a href="{{ route('achats.commandes.index') }}" class="btn btn-sm btn-outline-secondary">✕</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">{{ $commandes->total() }} bon(s) de commande</span>
        @can('achats.commandes.creer')
        <a href="{{ route('achats.commandes.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouveau BC
        </a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Numéro</th>
                    <th>Fournisseur</th>
                    <th>Date</th>
                    <th>Livr. souhaitée</th>
                    <th class="text-end">Montant TTC</th>
                    <th>Réception</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($commandes as $bc)
                <tr>
                    <td><a href="{{ route('achats.commandes.show', $bc) }}" class="fw-semibold text-decoration-none">{{ $bc->numero }}</a></td>
                    <td>{{ $bc->fournisseur->nom }}</td>
                    <td>{{ $bc->date_commande->format('d/m/Y') }}</td>
                    <td>{{ $bc->date_livraison_souhaitee?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-end">{{ number_format($bc->montant_ttc, 0, ',', ' ') }} FCFA</td>
                    <td>
                        @if($bc->statut !== 'brouillon')
                        <div class="progress" style="height:6px; width:80px">
                            <div class="progress-bar bg-success" style="width:{{ $bc->taux_reception }}%"></div>
                        </div>
                        <small class="text-muted">{{ $bc->taux_reception }}%</small>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $bc->statut_badge }}">{{ $bc->statut_label }}</span></td>
                    <td>
                        <a href="{{ route('achats.commandes.show', $bc) }}" class="btn btn-xs btn-outline-secondary btn-sm">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($bc->statut === 'confirmee' || $bc->statut === 'partiellement_recue')
                        <a href="{{ route('achats.receptions.create', ['bon_commande_id' => $bc->id]) }}" class="btn btn-xs btn-outline-success btn-sm">
                            <i class="bi bi-box-arrow-in-down"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucun bon de commande</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($commandes->hasPages())
    <div class="card-footer">{{ $commandes->links() }}</div>
    @endif
</div>
@endsection
