@extends('layouts.app')

@section('title', 'Ventes')
@section('page-title', 'Ventes & Facturation')

@section('content')

{{-- Stats du mois --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-success bg-opacity-10">
                    <i class="bi bi-currency-exchange fs-3 text-success"></i>
                </div>
                <div>
                    <div class="fs-5 fw-bold">{{ number_format($stats['total_mois'] / 1000, 0) }}K FCFA</div>
                    <div class="text-muted small">CA ce mois</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-primary bg-opacity-10">
                    <i class="bi bi-cart-check fs-3 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold">{{ $stats['nb_mois'] }}</div>
                    <div class="text-muted small">Ventes ce mois</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-warning bg-opacity-10">
                    <i class="bi bi-exclamation-circle fs-3 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-warning">{{ $stats['impayees'] }}</div>
                    <div class="text-muted small">Factures impayées</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-danger bg-opacity-10">
                    <i class="bi bi-wallet2 fs-3 text-danger"></i>
                </div>
                <div>
                    <div class="fs-5 fw-bold text-danger">{{ number_format($stats['impayees_montant'] / 1000, 0) }}K FCFA</div>
                    <div class="text-muted small">Reste à encaisser</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    @foreach(['brouillon' => 'Brouillon', 'confirmee' => 'Confirmée', 'livree' => 'Livrée', 'facturee' => 'Facturée', 'payee' => 'Payée', 'annulee' => 'Annulée'] as $v => $l)
                        <option value="{{ $v }}" {{ request('statut') === $v ? 'selected' : '' }}>{{ $l }}</option>
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
                <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('ventes.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('ventes.create') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-cart-plus me-1"></i>Nouvelle vente
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
                    <th>N° Facture</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th class="text-end">Sous-total HT</th>
                    <th class="text-end">Total TTC</th>
                    <th class="text-end">Payé</th>
                    <th>Mode paiement</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventes as $v)
                <tr>
                    <td>
                        <a href="{{ route('ventes.show', $v) }}" class="fw-medium text-decoration-none">
                            {{ $v->numero_facture }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('clients.show', $v->client) }}" class="text-decoration-none text-dark">
                            {{ $v->client->nom }}
                        </a>
                    </td>
                    <td class="small text-muted">{{ $v->date_vente->format('d/m/Y') }}</td>
                    <td class="text-end small">{{ number_format($v->sous_total_ht, 0, ',', ' ') }}</td>
                    <td class="text-end fw-semibold">{{ number_format($v->total_ttc, 0, ',', ' ') }} FCFA</td>
                    <td class="text-end small {{ $v->est_soldee ? 'text-success' : 'text-warning' }}">
                        {{ number_format($v->montant_paye, 0, ',', ' ') }}
                        @if(! $v->est_soldee && $v->statut !== 'annulee')
                            <br><span class="text-danger" style="font-size:.75rem">
                                reste {{ number_format($v->reste_a_payer, 0, ',', ' ') }}
                            </span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $v->mode_paiement_label }}</td>
                    <td><span class="badge bg-{{ $v->statut_badge }}">{{ $v->statut_label }}</span></td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('ventes.show', $v) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('ventes.facture', $v) }}" class="btn btn-outline-secondary" title="Imprimer facture"><i class="bi bi-printer"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-receipt fs-1 d-block mb-2"></i>Aucune vente enregistrée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $ventes->links() }}</div>
@endsection
