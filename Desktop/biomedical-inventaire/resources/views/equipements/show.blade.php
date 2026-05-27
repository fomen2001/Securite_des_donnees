@extends('layouts.app')

@section('title', $equipement->designation)
@section('page-title', $equipement->designation)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('equipements.index') }}">Inventaire</a></li>
    <li class="breadcrumb-item active">{{ $equipement->code_inventaire }}</li>
@endsection

@section('content')

<div class="row g-4">

    {{-- Colonne gauche : fiche équipement --}}
    <div class="col-lg-4">

        {{-- Image et état --}}
        <div class="card shadow-sm mb-3 text-center">
            @if($equipement->image)
                <img src="{{ Storage::url($equipement->image) }}" class="card-img-top" style="max-height:200px;object-fit:contain;padding:1rem">
            @else
                <div class="py-4 text-muted">
                    <i class="bi bi-image fs-1"></i>
                    <p class="small mt-2">Aucune image</p>
                </div>
            @endif
            <div class="card-body pt-0">
                <h5 class="card-title">{{ $equipement->designation }}</h5>
                <code class="small text-muted">{{ $equipement->code_inventaire }}</code>
                <div class="mt-2">
                    <span class="badge bg-{{ $equipement->etat_badge }} fs-6">{{ $equipement->etat_label }}</span>
                </div>
                @if($equipement->classe_risque)
                    <div class="mt-1"><span class="badge bg-secondary">Classe {{ $equipement->classe_risque }}</span></div>
                @endif
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold small">Actions rapides</div>
            <div class="list-group list-group-flush">
                <a href="{{ route('equipements.edit', $equipement) }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-pencil me-2 text-secondary"></i>Modifier l'équipement
                </a>
                <a href="{{ route('mouvements.create', ['equipement_id' => $equipement->id]) }}"
                   class="list-group-item list-group-item-action">
                    <i class="bi bi-arrow-left-right me-2 text-info"></i>Enregistrer un mouvement
                </a>
                <a href="{{ route('maintenances.create', ['equipement_id' => $equipement->id]) }}"
                   class="list-group-item list-group-item-action">
                    <i class="bi bi-tools me-2 text-warning"></i>Planifier une maintenance
                </a>
                <form method="POST" action="{{ route('equipements.destroy', $equipement) }}"
                      onsubmit="return confirm('Archiver cet équipement ?')">
                    @csrf @method('DELETE')
                    <button class="list-group-item list-group-item-action text-danger border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-archive me-2"></i>Archiver
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Colonne droite : détails --}}
    <div class="col-lg-8">

        {{-- Détails techniques --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-2 text-primary"></i>Fiche technique
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th class="text-muted small" width="45%">Marque</th><td>{{ $equipement->marque ?? '—' }}</td></tr>
                            <tr><th class="text-muted small">Modèle</th><td>{{ $equipement->modele ?? '—' }}</td></tr>
                            <tr><th class="text-muted small">N° série</th><td><code>{{ $equipement->numero_serie ?? '—' }}</code></td></tr>
                            <tr><th class="text-muted small">N° lot</th><td>{{ $equipement->numero_lot ?? '—' }}</td></tr>
                            <tr><th class="text-muted small">Catégorie</th>
                                <td>
                                    @if($equipement->categorie)
                                        <span class="badge" style="background:{{ $equipement->categorie->couleur }}">
                                            {{ $equipement->categorie->nom }}
                                        </span>
                                    @else —
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-sm-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th class="text-muted small" width="45%">Service</th><td>{{ $equipement->service?->nom ?? '—' }}</td></tr>
                            <tr><th class="text-muted small">Fournisseur</th><td>{{ $equipement->fournisseur?->nom ?? '—' }}</td></tr>
                            <tr><th class="text-muted small">Acquisition</th><td>{{ $equipement->date_acquisition?->format('d/m/Y') ?? '—' }}</td></tr>
                            <tr><th class="text-muted small">Fin garantie</th>
                                <td class="{{ $equipement->garantie_expiree ? 'text-danger' : '' }}">
                                    {{ $equipement->date_fin_garantie?->format('d/m/Y') ?? '—' }}
                                    @if($equipement->garantie_expiree) <i class="bi bi-shield-x ms-1"></i> @endif
                                </td>
                            </tr>
                            <tr><th class="text-muted small">Prix achat</th>
                                <td>{{ $equipement->prix_achat ? number_format($equipement->prix_achat, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($equipement->description)
                    <hr>
                    <p class="mb-0 small text-muted">{{ $equipement->description }}</p>
                @endif
            </div>
        </div>

        {{-- Stock --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-box-seam me-2 text-success"></i>Stock
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="fs-2 fw-bold {{ $equipement->stock_alert ? 'text-danger' : 'text-success' }}">
                            {{ $equipement->quantite }}
                        </div>
                        <div class="small text-muted">En stock</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-2 fw-bold text-secondary">{{ $equipement->quantite_min }}</div>
                        <div class="small text-muted">Seuil alerte</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-2 fw-bold {{ $equipement->maintenance_echue ? 'text-danger' : 'text-info' }}">
                            {{ $equipement->prochaine_maintenance?->format('d/m/Y') ?? '—' }}
                        </div>
                        <div class="small text-muted">Proch. maintenance</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Onglets mouvements / maintenances --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs card-header-tabs ms-1" id="tabsEq" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabMouvements">
                            <i class="bi bi-arrow-left-right me-1"></i>Mouvements ({{ $equipement->mouvements->count() }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMaintenances">
                            <i class="bi bi-tools me-1"></i>Maintenances ({{ $equipement->maintenances->count() }})
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tabMouvements">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Type</th><th>Qté</th><th>Avant</th><th>Après</th><th>Motif</th><th>Par</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                @forelse($equipement->mouvements as $mv)
                                <tr>
                                    <td><span class="badge bg-{{ $mv->type_badge }}">{{ $mv->type_label }}</span></td>
                                    <td>{{ $mv->quantite }}</td>
                                    <td class="text-muted">{{ $mv->quantite_avant }}</td>
                                    <td class="fw-semibold">{{ $mv->quantite_apres }}</td>
                                    <td class="small text-muted">{{ Str::limit($mv->motif, 40) }}</td>
                                    <td class="small text-muted">{{ $mv->user->name }}</td>
                                    <td class="small text-muted">{{ $mv->date_mouvement->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">Aucun mouvement</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="tabMaintenances">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Type</th><th>Statut</th><th>Planifiée</th><th>Technicien</th><th>Coût</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse($equipement->maintenances as $m)
                                <tr>
                                    <td>{{ $m->type_label }}</td>
                                    <td><span class="badge bg-{{ $m->statut_badge }}">{{ ucfirst($m->statut) }}</span></td>
                                    <td class="small">{{ $m->date_planifiee->format('d/m/Y') }}</td>
                                    <td class="small text-muted">{{ $m->technicien ?? '—' }}</td>
                                    <td class="small">{{ $m->cout ? number_format($m->cout, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                                    <td>
                                        <a href="{{ route('maintenances.show', $m) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">Aucune maintenance</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
