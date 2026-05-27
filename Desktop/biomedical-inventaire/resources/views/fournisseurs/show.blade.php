@extends('layouts.app')

@section('title', $fournisseur->nom)
@section('page-title', $fournisseur->nom)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('fournisseurs.index') }}">Fournisseurs</a></li>
    <li class="breadcrumb-item active">{{ $fournisseur->nom }}</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- Fiche fournisseur --}}
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-truck me-2 text-primary"></i>Fiche fournisseur</strong>
                <span class="badge bg-{{ $fournisseur->statut === 'actif' ? 'success' : 'secondary' }}">
                    {{ ucfirst($fournisseur->statut) }}
                </span>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted small w-40">Contact</th>
                        <td>{{ $fournisseur->contact_nom ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted small">Email</th>
                        <td>
                            @if($fournisseur->email)
                                <a href="mailto:{{ $fournisseur->email }}">{{ $fournisseur->email }}</a>
                            @else —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted small">Téléphone</th>
                        <td>{{ $fournisseur->telephone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted small">Pays</th>
                        <td>{{ $fournisseur->pays ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted small">Adresse</th>
                        <td class="small">{{ $fournisseur->adresse ?? '—' }}</td>
                    </tr>
                    @if($fournisseur->site_web)
                    <tr>
                        <th class="text-muted small">Site web</th>
                        <td>
                            <a href="{{ $fournisseur->site_web }}" target="_blank" class="small">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Visiter
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="card-footer bg-white d-flex gap-2">
                <a href="{{ route('fournisseurs.edit', $fournisseur) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <form method="POST" action="{{ route('fournisseurs.destroy', $fournisseur) }}"
                      onsubmit="return confirm('Archiver ce fournisseur ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-archive me-1"></i>Archiver
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Équipements fournis --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-clipboard2-pulse me-2 text-info"></i>
                Équipements fournis ({{ $fournisseur->equipements->count() }})
            </div>
            @if($fournisseur->equipements->isEmpty())
                <div class="card-body text-center text-muted py-4">Aucun équipement associé</div>
            @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Code</th><th>Désignation</th><th>Catégorie</th><th>Service</th><th>État</th></tr>
                    </thead>
                    <tbody>
                        @foreach($fournisseur->equipements as $eq)
                        <tr>
                            <td><code class="small">{{ $eq->code_inventaire }}</code></td>
                            <td>
                                <a href="{{ route('equipements.show', $eq) }}" class="text-decoration-none">
                                    {{ $eq->designation }}
                                </a>
                            </td>
                            <td class="small text-muted">{{ $eq->categorie?->nom ?? '—' }}</td>
                            <td class="small text-muted">{{ $eq->service?->nom ?? '—' }}</td>
                            <td><span class="badge bg-{{ $eq->etat_badge }}">{{ $eq->etat_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Maintenances réalisées --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-tools me-2 text-warning"></i>
                Maintenances réalisées ({{ $fournisseur->maintenances->count() }})
            </div>
            @if($fournisseur->maintenances->isEmpty())
                <div class="card-body text-center text-muted py-4">Aucune maintenance</div>
            @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Équipement</th><th>Type</th><th>Date</th><th>Statut</th><th>Coût</th></tr>
                    </thead>
                    <tbody>
                        @foreach($fournisseur->maintenances as $m)
                        <tr>
                            <td class="small">
                                <a href="{{ route('maintenances.show', $m) }}" class="text-decoration-none">
                                    {{ Str::limit($m->equipement->designation, 30) }}
                                </a>
                            </td>
                            <td class="small text-muted">{{ $m->type_label }}</td>
                            <td class="small text-muted">{{ $m->date_planifiee->format('d/m/Y') }}</td>
                            <td><span class="badge bg-{{ $m->statut_badge }}">{{ ucfirst($m->statut) }}</span></td>
                            <td class="small">{{ $m->cout ? number_format($m->cout, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
