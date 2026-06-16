@extends('layouts.app')

@section('title', 'Bons de livraison')
@section('page-title', 'Bons de livraison clients')
@section('breadcrumb')
    <li class="breadcrumb-item active">Achats</li>
    <li class="breadcrumb-item active">Livraisons</li>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Ce mois</div>
                <div class="fw-bold fs-4">{{ $stats['ce_mois'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Préparés</div>
                <div class="fw-bold fs-4 text-secondary">{{ $stats['prepares'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Expédiés</div>
                <div class="fw-bold fs-4 text-info">{{ $stats['expedies'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Livrés ce mois</div>
                <div class="fw-bold fs-4 text-success">{{ $stats['livres'] }}</div>
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
                    @foreach(['prepare'=>'Préparé','expedie'=>'Expédié','livre'=>'Livré','retourne'=>'Retourné','annule'=>'Annulé'] as $val => $label)
                        <option value="{{ $val }}" {{ request('statut') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="client_id" class="form-select form-select-sm">
                    <option value="">Tous les clients</option>
                    @foreach($clients as $id => $nom)
                        <option value="{{ $id }}" {{ request('client_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
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
                <a href="{{ route('achats.livraisons.index') }}" class="btn btn-sm btn-outline-secondary">✕</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">{{ $livraisons->total() }} bon(s) de livraison</span>
        @can('achats.livraisons.creer')
        <a href="{{ route('achats.livraisons.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouveau BL
        </a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Numéro</th>
                    <th>Client</th>
                    <th>Vente liée</th>
                    <th>Date livraison</th>
                    <th>Transporteur</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($livraisons as $bl)
                <tr>
                    <td><a href="{{ route('achats.livraisons.show', $bl) }}" class="fw-semibold text-decoration-none">{{ $bl->numero }}</a></td>
                    <td>{{ $bl->client->nom }}</td>
                    <td>
                        @if($bl->vente)
                        <a href="{{ route('ventes.show', $bl->vente_id) }}" class="text-decoration-none small">
                            {{ $bl->vente->numero_facture }}
                        </a>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>{{ $bl->date_livraison->format('d/m/Y') }}</td>
                    <td>{{ $bl->transporteur ?? '—' }}</td>
                    <td><span class="badge bg-{{ $bl->statut_badge }}">{{ $bl->statut_label }}</span></td>
                    <td>
                        <a href="{{ route('achats.livraisons.show', $bl) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun bon de livraison</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($livraisons->hasPages())
    <div class="card-footer">{{ $livraisons->links() }}</div>
    @endif
</div>
@endsection
