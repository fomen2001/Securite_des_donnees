@extends('layouts.app')
@section('title', 'Enregistrer un visiteur')
@section('page-title', 'Enregistrer un visiteur')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('secretariat.visiteurs.index') }}">Visiteurs</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<form action="{{ route('secretariat.visiteurs.store') }}" method="POST">
@csrf

<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-person-badge me-2 text-primary"></i>Identité du visiteur</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                <input type="text" name="nom" value="{{ old('nom') }}" class="form-control @error('nom') is-invalid @enderror"
                       placeholder="Nom de famille" required autofocus>
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Prénom</label>
                <input type="text" name="prenom" value="{{ old('prenom') }}" class="form-control" placeholder="Prénom">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Entreprise / Organisation</label>
                <input type="text" name="entreprise" value="{{ old('entreprise') }}" class="form-control"
                       placeholder="Nom de l'entreprise représentée">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Téléphone</label>
                <input type="text" name="telephone" value="{{ old('telephone') }}" class="form-control"
                       placeholder="+237 6XX XXX XXX">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control">
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-building me-2 text-success"></i>Objet de la visite</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Objet / Motif <span class="text-danger">*</span></label>
                <input type="text" name="objet_visite" value="{{ old('objet_visite') }}"
                       class="form-control @error('objet_visite') is-invalid @enderror"
                       placeholder="Ex: Réunion commerciale, livraison, entretien technique…" required>
                @error('objet_visite')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Personne à rencontrer <span class="text-danger">*</span></label>
                <select name="employe_id" class="form-select" onchange="majPersonne(this)">
                    <option value="">— Sélectionner un employé —</option>
                    @foreach($employes as $e)
                    <option value="{{ $e->id }}" data-nom="{{ $e->prenom }} {{ $e->nom }}"
                            {{ old('employe_id') == $e->id ? 'selected' : '' }}>
                        {{ $e->prenom }} {{ $e->nom }} — {{ $e->poste ?? $e->service?->nom ?? '' }}
                    </option>
                    @endforeach
                    <option value="">— Ou saisir manuellement —</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nom de la personne visitée <span class="text-danger">*</span></label>
                <input type="text" name="personne_visitee" id="inp-personne-visitee"
                       value="{{ old('personne_visitee') }}"
                       class="form-control @error('personne_visitee') is-invalid @enderror"
                       placeholder="Saisir si non listé ci-contre" required>
                @error('personne_visitee')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date & heure d'entrée <span class="text-danger">*</span></label>
                <input type="datetime-local" name="date_entree"
                       value="{{ old('date_entree', now()->format('Y-m-d\TH:i')) }}"
                       class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">N° badge / laissez-passer</label>
                <input type="text" name="badge_numero" value="{{ old('badge_numero') }}"
                       class="form-control" placeholder="Ex: B-042">
            </div>
            <div class="col-12">
                <label class="form-label">Observations</label>
                <textarea name="observations" class="form-control" rows="2"
                          placeholder="Remarques, documents apportés…">{{ old('observations') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('secretariat.visiteurs.index') }}" class="btn btn-outline-secondary">Annuler</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-person-check me-1"></i>Enregistrer le visiteur
    </button>
</div>
</form>
</div>
</div>
@endsection

@push('scripts')
<script>
function majPersonne(sel) {
    const opt = sel.options[sel.selectedIndex];
    const nom = opt.dataset.nom || '';
    if (nom) document.getElementById('inp-personne-visitee').value = nom;
}
</script>
@endpush
