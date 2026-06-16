@extends('layouts.app')

@section('title', 'Bons de réception')
@section('page-title', 'Bons de réception')
@section('breadcrumb')
    <li class="breadcrumb-item active">Achats</li>
    <li class="breadcrumb-item active">Réceptions</li>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Ce mois</div>
                <div class="fw-bold fs-4">{{ $stats['ce_mois'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">En attente de validation</div>
                <div class="fw-bold fs-4 text-warning">{{ $stats['en_attente'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Validés ce mois</div>
                <div class="fw-bold fs-4 text-success">{{ $stats['valides'] }}</div>
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
                    @foreach(['en_attente'=>'En attente','valide'=>'Validé','rejete'=>'Rejeté'] as $val => $label)
                        <option value="{{ $val }}" {{ request('statut') === $val ? 'selected' : '' }}>{{ $label }}</option>
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
                <a href="{{ route('achats.receptions.index') }}" class="btn btn-sm btn-outline-secondary">✕</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">{{ $receptions->total() }} bon(s) de réception</span>
        @can('achats.receptions.creer')
        <a href="{{ route('achats.receptions.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouveau BR
        </a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Numéro</th>
                    <th>Bon de commande</th>
                    <th>Fournisseur</th>
                    <th>Date réception</th>
                    <th>BL Fournisseur</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($receptions as $br)
                <tr>
                    <td><a href="{{ route('achats.receptions.show', $br) }}" class="fw-semibold text-decoration-none">{{ $br->numero }}</a></td>
                    <td><a href="{{ route('achats.commandes.show', $br->bon_commande_id) }}" class="text-decoration-none">{{ $br->bonCommande->numero }}</a></td>
                    <td>{{ $br->fournisseur->nom }}</td>
                    <td>{{ $br->date_reception->format('d/m/Y') }}</td>
                    <td>{{ $br->numero_bl_fournisseur ?? '—' }}</td>
                    <td><span class="badge bg-{{ $br->statut_badge }}">{{ $br->statut_label }}</span></td>
                    <td>
                        <a href="{{ route('achats.receptions.show', $br) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun bon de réception</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($receptions->hasPages())
    <div class="card-footer">{{ $receptions->links() }}</div>
    @endif
</div>
@endsection
