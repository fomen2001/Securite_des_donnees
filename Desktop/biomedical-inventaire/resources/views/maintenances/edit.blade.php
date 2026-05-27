@extends('layouts.app')

@section('title', 'Modifier maintenance')
@section('page-title', 'Modifier la maintenance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('maintenances.index') }}">Maintenances</a></li>
    <li class="breadcrumb-item"><a href="{{ route('maintenances.show', $maintenance) }}">#{{ $maintenance->id }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<form method="POST" action="{{ route('maintenances.update', $maintenance) }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-pencil me-2 text-warning"></i>Modifier la maintenance
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Équipement <span class="text-danger">*</span></label>
                    <select name="equipement_id" class="form-select @error('equipement_id') is-invalid @enderror" required>
                        @foreach($equipements as $eq)
                            <option value="{{ $eq->id }}"
                                    {{ old('equipement_id', $maintenance->equipement_id) == $eq->id ? 'selected' : '' }}>
                                [{{ $eq->code_inventaire }}] {{ $eq->designation }}
                            </option>
                        @endforeach
                    </select>
                    @error('equipement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-select" required>
                        @foreach(['planifiee' => 'Planifiée', 'en_cours' => 'En cours', 'terminee' => 'Terminée', 'annulee' => 'Annulée'] as $val => $label)
                            <option value="{{ $val }}" {{ old('statut', $maintenance->statut) === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        @foreach(['preventive' => 'Préventive', 'corrective' => 'Corrective', 'calibration' => 'Calibration', 'verification' => 'Vérification'] as $val => $label)
                            <option value="{{ $val }}" {{ old('type', $maintenance->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date planifiée <span class="text-danger">*</span></label>
                    <input type="date" name="date_planifiee" class="form-control"
                        value="{{ old('date_planifiee', $maintenance->date_planifiee->format('Y-m-d')) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Prochaine maintenance</label>
                    <input type="date" name="prochaine_maintenance" class="form-control"
                        value="{{ old('prochaine_maintenance', $maintenance->prochaine_maintenance?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_debut" class="form-control"
                        value="{{ old('date_debut', $maintenance->date_debut?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_fin" class="form-control"
                        value="{{ old('date_fin', $maintenance->date_fin?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Technicien</label>
                    <input type="text" name="technicien" class="form-control"
                        value="{{ old('technicien', $maintenance->technicien) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Prestataire externe</label>
                    <select name="fournisseur_id" class="form-select">
                        <option value="">Aucun (interne)</option>
                        @foreach($fournisseurs as $id => $nom)
                            <option value="{{ $id }}" {{ old('fournisseur_id', $maintenance->fournisseur_id) == $id ? 'selected' : '' }}>{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Coût (FCFA)</label>
                    <input type="number" name="cout" class="form-control"
                        value="{{ old('cout', $maintenance->cout) }}" min="0" step="0.01">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Nouveau rapport PDF</label>
                    <input type="file" name="rapport" class="form-control" accept=".pdf">
                    @if($maintenance->rapport_path)
                        <div class="form-text">
                            <i class="bi bi-file-pdf text-danger"></i>
                            Rapport existant —
                            <a href="{{ Storage::url($maintenance->rapport_path) }}" target="_blank">Voir</a>
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    <label class="form-label">Description des travaux</label>
                    <textarea name="description_travaux" class="form-control" rows="3">{{ old('description_travaux', $maintenance->description_travaux) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Observations</label>
                    <textarea name="observations" class="form-control" rows="2">{{ old('observations', $maintenance->observations) }}</textarea>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="equipement_operationnel" value="0">
                        <input class="form-check-input" type="checkbox" name="equipement_operationnel"
                               value="1" id="opCheck"
                               {{ old('equipement_operationnel', $maintenance->equipement_operationnel) ? 'checked' : '' }}>
                        <label class="form-check-label" for="opCheck">
                            Équipement opérationnel après maintenance
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('maintenances.show', $maintenance) }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</form>
</div>
</div>
@endsection
