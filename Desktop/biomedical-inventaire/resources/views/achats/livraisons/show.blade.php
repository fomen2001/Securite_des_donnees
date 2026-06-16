@extends('layouts.app')

@section('title', $bonLivraison->numero)
@section('page-title', 'Bon de livraison — ' . $bonLivraison->numero)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achats.livraisons.index') }}">Livraisons</a></li>
    <li class="breadcrumb-item active">{{ $bonLivraison->numero }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-truck me-2"></i>{{ $bonLivraison->numero }}</span>
                <span class="badge bg-{{ $bonLivraison->statut_badge }} fs-6">{{ $bonLivraison->statut_label }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="text-muted small">Client</div>
                        <div class="fw-semibold">{{ $bonLivraison->client->nom }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Date de livraison</div>
                        <div class="fw-semibold">{{ $bonLivraison->date_livraison->format('d/m/Y') }}</div>
                    </div>
                    @if($bonLivraison->vente)
                    <div class="col-md-4">
                        <div class="text-muted small">Vente liée</div>
                        <div>
                            <a href="{{ route('ventes.show', $bonLivraison->vente_id) }}" class="text-decoration-none">
                                {{ $bonLivraison->vente->numero_facture }}
                            </a>
                        </div>
                    </div>
                    @endif
                    @if($bonLivraison->adresse_livraison)
                    <div class="col-md-6">
                        <div class="text-muted small">Adresse de livraison</div>
                        <div>{{ $bonLivraison->adresse_livraison }}</div>
                    </div>
                    @endif
                    @if($bonLivraison->transporteur)
                    <div class="col-md-3">
                        <div class="text-muted small">Transporteur</div>
                        <div>{{ $bonLivraison->transporteur }}</div>
                    </div>
                    @endif
                    @if($bonLivraison->contact_reception)
                    <div class="col-md-3">
                        <div class="text-muted small">Contact réception</div>
                        <div>{{ $bonLivraison->contact_reception }}</div>
                    </div>
                    @endif
                </div>

                {{-- Lignes --}}
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Article</th>
                                <th>Référence</th>
                                <th class="text-center">Quantité</th>
                                <th>Unité</th>
                                <th>Observations</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bonLivraison->lignes as $l)
                            <tr>
                                <td>{{ $l->designation }}</td>
                                <td class="text-muted small">{{ $l->reference ?? '—' }}</td>
                                <td class="text-center fw-semibold">{{ $l->quantite }}</td>
                                <td>{{ $l->unite }}</td>
                                <td class="text-muted small">{{ $l->observations ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($bonLivraison->observations)
                <div class="alert alert-light border mt-3">
                    <i class="bi bi-chat-text me-1"></i> {{ $bonLivraison->observations }}
                </div>
                @endif
            </div>
        </div>

        {{-- Zone signature (impression) --}}
        @if(in_array($bonLivraison->statut, ['livre', 'expedie']))
        <div class="card border-0 bg-light">
            <div class="card-body">
                <div class="row text-center mt-3">
                    <div class="col-6 border-end">
                        <div class="text-muted small mb-3">Signature expéditeur</div>
                        <div style="height:50px;border-bottom:1px solid #ccc;"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small mb-3">Signature récepteur</div>
                        <div style="height:50px;border-bottom:1px solid #ccc;"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                @can('achats.livraisons.expedier')
                @if($bonLivraison->statut === 'prepare')
                <form action="{{ route('achats.livraisons.expedier', $bonLivraison) }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="btn btn-info w-100 text-white">
                        <i class="bi bi-truck me-1"></i>Marquer comme expédié
                    </button>
                </form>
                @endif

                @if($bonLivraison->statut === 'expedie')
                <form action="{{ route('achats.livraisons.livrer', $bonLivraison) }}" method="POST"
                      onsubmit="return confirm('Confirmer la livraison ?')">
                    @csrf @method('PATCH')
                    <button class="btn btn-success w-100">
                        <i class="bi bi-check-circle me-1"></i>Confirmer livraison
                    </button>
                </form>
                @endif

                @if(in_array($bonLivraison->statut, ['prepare', 'expedie']))
                <form action="{{ route('achats.livraisons.annuler', $bonLivraison) }}" method="POST"
                      onsubmit="return confirm('Annuler ce bon de livraison ?')">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-danger w-100">
                        <i class="bi bi-x-circle me-1"></i>Annuler
                    </button>
                </form>
                @endif
                @endcan

                @if($bonLivraison->statut === 'livre')
                <div class="alert alert-success small mb-0">
                    <i class="bi bi-check-circle me-1"></i>Livraison confirmée
                </div>
                @endif

                <a href="{{ route('achats.livraisons.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body small text-muted">
                Créé par {{ $bonLivraison->user->name }}<br>
                le {{ $bonLivraison->created_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</div>
@endsection
