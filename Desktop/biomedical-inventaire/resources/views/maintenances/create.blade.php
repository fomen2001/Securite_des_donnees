@extends('layouts.app')

@section('title', 'Nouvelle maintenance')
@section('page-title', 'Planifier une maintenance')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<form method="POST" action="{{ route('maintenances.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-tools me-2 text-warning"></i>Nouvelle maintenance
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Équipement <span class="text-danger">*</span></label>
                    <select name="equipement_id" class="form-select @error('equipement_id') is-invalid @enderror" required>
                        <option value="">Sélectionner un équipement...</option>
                        @foreach($equipements as $eq)
                            <option value="{{ $eq->id }}"
                                    {{ (old('equipement_id', $equipement?->id)) == $eq->id ? 'selected' : '' }}>
                                [{{ $eq->code_inventaire }}] {{ $eq->designation }}
                            </option>
                        @endforeach
                    </select>
                    @error('equipement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-select" required>
                        <option value="planifiee" {{ old('statut', 'planifiee') === 'planifiee' ? 'selected' : '' }}>Planifiée</option>
                        <option value="en_cours" {{ old('statut') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="terminee" {{ old('statut') === 'terminee' ? 'selected' : '' }}>Terminée</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="preventive" {{ old('type', 'preventive') === 'preventive' ? 'selected' : '' }}>Préventive</option>
                        <option value="corrective" {{ old('type') === 'corrective' ? 'selected' : '' }}>Corrective</option>
                        <option value="calibration" {{ old('type') === 'calibration' ? 'selected' : '' }}>Calibration</option>
                        <option value="verification" {{ old('type') === 'verification' ? 'selected' : '' }}>Vérification</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date planifiée <span class="text-danger">*</span></label>
                    <input type="date" name="date_planifiee" class="form-control @error('date_planifiee') is-invalid @enderror"
                        value="{{ old('date_planifiee') }}" required>
                    @error('date_planifiee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Prochaine maintenance</label>
                    <input type="date" name="prochaine_maintenance" class="form-control"
                        value="{{ old('prochaine_maintenance') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Technicien</label>
                    <input type="text" name="technicien" class="form-control"
                        value="{{ old('technicien') }}" placeholder="Nom du technicien">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Prestataire externe</label>
                    <select name="fournisseur_id" class="form-select">
                        <option value="">Aucun (interne)</option>
                        @foreach($fournisseurs as $id => $nom)
                            <option value="{{ $id }}" {{ old('fournisseur_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Coût (FCFA)</label>
                    <input type="number" name="cout" class="form-control" value="{{ old('cout') }}" min="0" step="0.01">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Rapport PDF</label>
                    <input type="file" name="rapport" class="form-control" accept=".pdf">
                </div>

                <div class="col-12">
                    <label class="form-label">Description des travaux</label>
                    <textarea name="description_travaux" class="form-control" rows="3">{{ old('description_travaux') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Observations</label>
                    <textarea name="observations" class="form-control" rows="2">{{ old('observations') }}</textarea>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="equipement_operationnel" value="0">
                        <input class="form-check-input" type="checkbox" name="equipement_operationnel"
                               value="1" id="opCheck" {{ old('equipement_operationnel', 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="opCheck">
                            Équipement opérationnel après maintenance
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('maintenances.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</form>
</div>
</div>
@endsection
