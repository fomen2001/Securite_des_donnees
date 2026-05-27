@extends('layouts.app')

@section('title', 'Détail maintenance')
@section('page-title', 'Fiche de maintenance')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-{{ $maintenance->statut_badge }} me-2">{{ ucfirst($maintenance->statut) }}</span>
                <span class="badge bg-secondary">{{ $maintenance->type_label }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('maintenances.edit', $maintenance) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                @if($maintenance->rapport_path)
                    <a href="{{ Storage::url($maintenance->rapport_path) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-file-pdf me-1"></i>Rapport PDF
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <h5 class="mb-3">
                <i class="bi bi-tools me-2 text-warning"></i>
                {{ $maintenance->equipement->designation }}
                <span class="text-muted small ms-2">({{ $maintenance->equipement->code_inventaire }})</span>
            </h5>

            <div class="row g-3">
                <div class="col-sm-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th class="text-muted small w-50">Service</th>
                            <td>{{ $maintenance->equipement->service?->nom ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted small">Date planifiée</th>
                            <td>{{ $maintenance->date_planifiee->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted small">Date début</th>
                            <td>{{ $maintenance->date_debut?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted small">Date fin</th>
                            <td>{{ $maintenance->date_fin?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                        @if($maintenance->duree !== null)
                        <tr>
                            <th class="text-muted small">Durée</th>
                            <td>{{ $maintenance->duree }} jour(s)</td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="col-sm-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th class="text-muted small w-50">Technicien</th>
                            <td>{{ $maintenance->technicien ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted small">Prestataire</th>
                            <td>{{ $maintenance->fournisseur?->nom ?? 'Interne' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted small">Coût</th>
                            <td>{{ $maintenance->cout ? number_format($maintenance->cout, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted small">Éq. opérationnel</th>
                            <td>
                                @if($maintenance->equipement_operationnel)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-danger">Non</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted small">Prochaine</th>
                            <td>{{ $maintenance->prochaine_maintenance?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($maintenance->description_travaux)
            <div class="mt-3">
                <h6 class="fw-semibold">Travaux effectués</h6>
                <p class="text-muted">{{ $maintenance->description_travaux }}</p>
            </div>
            @endif

            @if($maintenance->observations)
            <div class="mt-2">
                <h6 class="fw-semibold">Observations</h6>
                <p class="text-muted">{{ $maintenance->observations }}</p>
            </div>
            @endif

            <div class="text-muted small mt-3">
                Enregistré par <strong>{{ $maintenance->user->name }}</strong>
                le {{ $maintenance->created_at->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('equipements.show', $maintenance->equipement) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour à l'équipement
        </a>
        <a href="{{ route('maintenances.index') }}" class="btn btn-outline-secondary btn-sm ms-2">
            Liste des maintenances
        </a>
    </div>
</div>
</div>
@endsection
