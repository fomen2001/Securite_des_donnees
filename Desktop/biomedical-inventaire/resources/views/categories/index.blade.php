@extends('layouts.app')

@section('title', 'Catégories')
@section('page-title', 'Catégories d\'équipements')

@section('content')
<div class="row g-4">

    {{-- Formulaire d'ajout --}}
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Nouvelle catégorie
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('categories.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                            value="{{ old('nom') }}" required placeholder="ex: Monitoring, Imagerie...">
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Couleur</label>
                        <input type="color" name="couleur" class="form-control form-control-color"
                            value="{{ old('couleur', '#0d6efd') }}">
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
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-tags me-2"></i>{{ $categories->total() }} catégorie(s)
            </div>
            <div class="list-group list-group-flush">
                @forelse($categories as $cat)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <span class="rounded-circle d-inline-block"
                                  style="width:14px;height:14px;background:{{ $cat->couleur }}"></span>
                            <div>
                                <strong>{{ $cat->nom }}</strong>
                                <span class="badge bg-light text-dark ms-2">{{ $cat->equipements_count }} équipement(s)</span>
                                @if($cat->description)
                                    <div class="small text-muted">{{ $cat->description }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal{{ $cat->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                  onsubmit="return confirm('Supprimer cette catégorie ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Modal d'édition --}}
                <div class="modal fade" id="editModal{{ $cat->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('categories.update', $cat) }}">
                                @csrf @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Modifier : {{ $cat->nom }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nom</label>
                                        <input type="text" name="nom" class="form-control" value="{{ $cat->nom }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ $cat->description }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Couleur</label>
                                        <input type="color" name="couleur" class="form-control form-control-color" value="{{ $cat->couleur }}">
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
                <div class="list-group-item text-muted text-center py-4">Aucune catégorie</div>
                @endforelse
            </div>
        </div>
        <div class="mt-3">{{ $categories->links() }}</div>
    </div>

</div>
@endsection
