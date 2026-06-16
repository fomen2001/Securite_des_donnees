@extends('layouts.app')

@section('title', $bonCommande->numero)
@section('page-title', 'Bon de commande — ' . $bonCommande->numero)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achats.commandes.index') }}">Bons de commande</a></li>
    <li class="breadcrumb-item active">{{ $bonCommande->numero }}</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- Colonne principale --}}
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-file-earmark-text me-2"></i>{{ $bonCommande->numero }}</span>
                <span class="badge bg-{{ $bonCommande->statut_badge }} fs-6">{{ $bonCommande->statut_label }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="text-muted small">Fournisseur</div>
                        <div class="fw-semibold">{{ $bonCommande->fournisseur->nom }}</div>
                        @if($bonCommande->fournisseur->contact_nom)
                        <div class="small text-muted">{{ $bonCommande->fournisseur->contact_nom }}</div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Date commande</div>
                        <div class="fw-semibold">{{ $bonCommande->date_commande->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Livraison souhaitée</div>
                        <div class="fw-semibold">{{ $bonCommande->date_livraison_souhaitee?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    @if($bonCommande->conditions)
                    <div class="col-12">
                        <div class="text-muted small">Conditions</div>
                        <div>{{ $bonCommande->conditions }}</div>
                    </div>
                    @endif
                </div>

                {{-- Lignes --}}
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Désignation</th>
                                <th>Réf. fournisseur</th>
                                <th class="text-center">Qté cmd.</th>
                                <th class="text-center">Qté reçue</th>
                                <th class="text-end">P.U. HT</th>
                                <th class="text-end">Total HT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bonCommande->lignes as $l)
                            <tr>
                                <td>
                                    {{ $l->designation }}
                                    @if($l->equipement)
                                    <div class="small text-muted">{{ $l->equipement->reference }}</div>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $l->reference_fournisseur ?? '—' }}</td>
                                <td class="text-center">{{ $l->quantite_commandee }} {{ $l->unite }}</td>
                                <td class="text-center">
                                    @if($bonCommande->statut !== 'brouillon')
                                    <span class="{{ $l->quantite_recue >= $l->quantite_commandee ? 'text-success fw-semibold' : 'text-warning' }}">
                                        {{ $l->quantite_recue }} / {{ $l->quantite_commandee }}
                                    </span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($l->prix_unitaire_ht, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($l->total_ht, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="fw-semibold">
                            <tr>
                                <td colspan="5" class="text-end text-muted">Total HT</td>
                                <td class="text-end">{{ number_format($bonCommande->montant_ht, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end text-muted">TVA ({{ $bonCommande->taux_tva }}%)</td>
                                <td class="text-end">{{ number_format($bonCommande->montant_tva, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="5" class="text-end fw-bold">TOTAL TTC</td>
                                <td class="text-end fw-bold fs-5">{{ number_format($bonCommande->montant_ttc, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($bonCommande->notes)
                <div class="alert alert-light border mt-3">
                    <i class="bi bi-info-circle me-1"></i> {{ $bonCommande->notes }}
                </div>
                @endif
            </div>
        </div>

        {{-- Réceptions liées --}}
        @if($bonCommande->receptions->count())
        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-box-arrow-in-down me-2"></i>Réceptions</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Numéro</th>
                            <th>Date</th>
                            <th>Transporteur</th>
                            <th>BL fournisseur</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bonCommande->receptions as $br)
                        <tr>
                            <td class="fw-semibold">{{ $br->numero }}</td>
                            <td>{{ $br->date_reception->format('d/m/Y') }}</td>
                            <td>{{ $br->transporteur ?? '—' }}</td>
                            <td>{{ $br->numero_bl_fournisseur ?? '—' }}</td>
                            <td><span class="badge bg-{{ $br->statut_badge }}">{{ $br->statut_label }}</span></td>
                            <td><a href="{{ route('achats.receptions.show', $br) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Colonne actions --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                @can('achats.receptions.creer')
                @if(in_array($bonCommande->statut, ['confirmee', 'partiellement_recue']))
                <a href="{{ route('achats.receptions.create', ['bon_commande_id' => $bonCommande->id]) }}"
                   class="btn btn-success">
                    <i class="bi bi-box-arrow-in-down me-1"></i>Saisir une réception
                </a>
                @endif
                @endcan

                @can('achats.commandes.modifier')
                @if($bonCommande->statut === 'brouillon')
                <form action="{{ route('achats.commandes.confirmer', $bonCommande) }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="btn btn-primary w-100"><i class="bi bi-send me-1"></i>Confirmer la commande</button>
                </form>
                @endif
                @endcan

                @can('achats.commandes.annuler')
                @if(in_array($bonCommande->statut, ['brouillon', 'confirmee']))
                <form action="{{ route('achats.commandes.annuler', $bonCommande) }}" method="POST"
                      onsubmit="return confirm('Annuler ce bon de commande ?')">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-danger w-100"><i class="bi bi-x-circle me-1"></i>Annuler</button>
                </form>
                @endif
                @endcan

                <a href="{{ route('achats.commandes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Retour à la liste
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Taux de réception</div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="progress flex-grow-1" style="height:12px">
                        <div class="progress-bar bg-success" style="width:{{ $bonCommande->taux_reception }}%"></div>
                    </div>
                    <span class="fw-bold">{{ $bonCommande->taux_reception }}%</span>
                </div>
                <div class="mt-2 small text-muted">
                    Créé par {{ $bonCommande->user->name }} le {{ $bonCommande->created_at->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
