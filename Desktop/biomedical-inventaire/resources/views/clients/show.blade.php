@extends('layouts.app')

@section('title', $client->nom)
@section('page-title', $client->nom)

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between">
                <strong><i class="bi bi-building me-2 text-primary"></i>{{ $client->type_label }}</strong>
                <span class="badge bg-{{ $client->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($client->statut) }}</span>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted small w-40">Code</th><td><code>{{ $client->code_client }}</code></td></tr>
                    <tr><th class="text-muted small">Contact</th><td>{{ $client->contact_nom ?? '—' }}</td></tr>
                    <tr><th class="text-muted small">Téléphone</th><td>{{ $client->telephone ?? '—' }}</td></tr>
                    <tr><th class="text-muted small">Email</th><td class="small">{{ $client->email ?? '—' }}</td></tr>
                    <tr><th class="text-muted small">Ville</th><td>{{ $client->ville ?? '—' }}</td></tr>
                    <tr><th class="text-muted small">N° Fiscal</th><td class="small">{{ $client->numero_contribuable ?? '—' }}</td></tr>
                </table>
                @if($client->notes)
                    <hr><p class="small text-muted mb-0">{{ $client->notes }}</p>
                @endif
            </div>
            <div class="card-footer bg-white d-flex gap-2">
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <a href="{{ route('ventes.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-cart-plus me-1"></i>Nouvelle vente
                </a>
            </div>
        </div>

        {{-- Statistiques --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold small">Statistiques</div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-4">
                        <div class="fs-4 fw-bold text-primary">{{ $stats['total_ventes'] }}</div>
                        <div class="text-muted" style="font-size:.7rem">Ventes</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-5 fw-bold text-success">{{ number_format($stats['chiffre_affaires'] / 1000, 0) }}K</div>
                        <div class="text-muted" style="font-size:.7rem">CA (FCFA)</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-4 fw-bold {{ $stats['impayees'] > 0 ? 'text-danger' : 'text-success' }}">{{ $stats['impayees'] }}</div>
                        <div class="text-muted" style="font-size:.7rem">Impayées</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-receipt me-2 text-success"></i>Historique des ventes</strong>
                <a href="{{ route('ventes.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-plus me-1"></i>Nouvelle vente
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Facture</th>
                            <th>Date</th>
                            <th class="text-end">Total TTC</th>
                            <th class="text-end">Payé</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->ventes as $v)
                        <tr>
                            <td><a href="{{ route('ventes.show', $v) }}" class="text-decoration-none fw-medium">{{ $v->numero_facture }}</a></td>
                            <td class="small text-muted">{{ $v->date_vente->format('d/m/Y') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($v->total_ttc, 0, ',', ' ') }} FCFA</td>
                            <td class="text-end small {{ $v->est_soldee ? 'text-success' : 'text-warning' }}">
                                {{ number_format($v->montant_paye, 0, ',', ' ') }} FCFA
                            </td>
                            <td><span class="badge bg-{{ $v->statut_badge }}">{{ $v->statut_label }}</span></td>
                            <td>
                                <a href="{{ route('ventes.facture', $v) }}" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-1" title="Facture">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-muted text-center py-3">Aucune vente</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
