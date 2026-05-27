@extends('layouts.app')

@section('title', 'Services')
@section('page-title', 'Services hospitaliers')

@section('content')
<div class="row g-4">

    {{-- Formulaire d'ajout --}}
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Nouveau service
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('services.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" value="{{ old('nom') }}"
                            required placeholder="ex: Cardiologie, Réanimation...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bâtiment</label>
                        <input type="text" name="batiment" class="form-control" value="{{ old('batiment') }}">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Étage</label>
                            <input type="text" name="etage" class="form-control" value="{{ old('etage') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Responsable</label>
                        <input type="text" name="responsable" class="form-control" value="{{ old('responsable') }}">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i>Créer
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Liste --}}
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Service</th>
                            <th>Bâtiment / Étage</th>
                            <th>Responsable</th>
                            <th class="text-center">Équipements</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $svc)
                        <tr>
                            <td>
                                <strong>{{ $svc->nom }}</strong>
                                @if($svc->telephone)
                                    <div class="small text-muted">{{ $svc->telephone }}</div>
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ $svc->batiment }} {{ $svc->etage ? '— ' . $svc->etage : '' }}
                            </td>
                            <td class="small text-muted">{{ $svc->responsable ?? '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('equipements.index', ['service_id' => $svc->id]) }}"
                                   class="badge bg-primary text-decoration-none">
                                    {{ $svc->equipements_count }}
                                </a>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal" data-bs-target="#editSvc{{ $svc->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('services.destroy', $svc) }}"
                                      class="d-inline" onsubmit="return confirm('Supprimer ce service ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        {{-- Modal édition --}}
                        <div class="modal fade" id="editSvc{{ $svc->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('services.update', $svc) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modifier : {{ $svc->nom }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Nom</label>
                                                <input type="text" name="nom" class="form-control" value="{{ $svc->nom }}" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Bâtiment</label>
                                                <input type="text" name="batiment" class="form-control" value="{{ $svc->batiment }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Étage</label>
                                                <input type="text" name="etage" class="form-control" value="{{ $svc->etage }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Responsable</label>
                                                <input type="text" name="responsable" class="form-control" value="{{ $svc->responsable }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Téléphone</label>
                                                <input type="text" name="telephone" class="form-control" value="{{ $svc->telephone }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucun service enregistré</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $services->links() }}</div>
    </div>
</div>
@endsection
