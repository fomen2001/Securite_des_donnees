@extends('layouts.app')

@section('title', $bonReception->numero)
@section('page-title', 'Bon de réception — ' . $bonReception->numero)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achats.receptions.index') }}">Réceptions</a></li>
    <li class="breadcrumb-item active">{{ $bonReception->numero }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-box-arrow-in-down me-2"></i>{{ $bonReception->numero }}</span>
                <span class="badge bg-{{ $bonReception->statut_badge }} fs-6">{{ $bonReception->statut_label }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="text-muted small">Bon de commande</div>
                        <div class="fw-semibold">
                            <a href="{{ route('achats.commandes.show', $bonReception->bonCommande) }}">
                                {{ $bonReception->bonCommande->numero }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Fournisseur</div>
                        <div class="fw-semibold">{{ $bonReception->fournisseur->nom }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Date de réception</div>
                        <div class="fw-semibold">{{ $bonReception->date_reception->format('d/m/Y') }}</div>
                    </div>
                    @if($bonReception->transporteur)
                    <div class="col-md-4">
                        <div class="text-muted small">Transporteur</div>
                        <div>{{ $bonReception->transporteur }}</div>
                    </div>
                    @endif
                    @if($bonReception->numero_bl_fournisseur)
                    <div class="col-md-4">
                        <div class="text-muted small">N° BL Fournisseur</div>
                        <div>{{ $bonReception->numero_bl_fournisseur }}</div>
                    </div>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Article</th>
                                <th class="text-center">Reçu</th>
                                <th class="text-center">Conforme</th>
                                <th class="text-center">Rejeté</th>
                                <th>Motif rejet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bonReception->lignes as $l)
                            <tr>
                                <td>
                                    {{ $l->designation }}
                                    @if($l->equipement)
                                    <div class="small text-muted">{{ $l->equipement->reference }}</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $l->quantite_recue }}</td>
                                <td class="text-center text-success fw-semibold">{{ $l->quantite_conforme }}</td>
                                <td class="text-center {{ $l->quantite_rejetee > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    {{ $l->quantite_rejetee }}
                                </td>
                                <td class="text-muted small">{{ $l->motif_rejet ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($bonReception->observations)
                <div class="alert alert-light border mt-3">
                    <i class="bi bi-chat-text me-1"></i> {{ $bonReception->observations }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                @can('achats.receptions.valider')
                @if($bonReception->statut === 'en_attente')
                <form action="{{ route('achats.receptions.valider', $bonReception) }}" method="POST"
                      onsubmit="return confirm('Valider cette réception et mettre à jour le stock ?')">
                    @csrf @method('PATCH')
                    <button class="btn btn-success w-100">
                        <i class="bi bi-check-circle me-1"></i>Valider et mettre à jour le stock
                    </button>
                </form>
                <form action="{{ route('achats.receptions.rejeter', $bonReception) }}" method="POST"
                      onsubmit="return confirm('Rejeter ce bon de réception ? Les quantités seront annulées.')">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-danger w-100">
                        <i class="bi bi-x-circle me-1"></i>Rejeter
                    </button>
                </form>
                @endif
                @endcan

                @if($bonReception->statut === 'valide')
                <div class="alert alert-success small mb-0">
                    <i class="bi bi-check-circle me-1"></i>Stock mis à jour le {{ $bonReception->updated_at->format('d/m/Y') }}
                </div>
                @endif

                <a href="{{ route('achats.commandes.show', $bonReception->bonCommande) }}" class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-text me-1"></i>Voir le BC
                </a>
                <a href="{{ route('achats.receptions.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body small text-muted">
                Créé par {{ $bonReception->user->name }}<br>
                le {{ $bonReception->created_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</div>
@endsection
