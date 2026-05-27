@extends('layouts.app')

@section('title', 'Modifier ' . $equipement->designation)
@section('page-title', 'Modifier l\'équipement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('equipements.index') }}">Inventaire</a></li>
    <li class="breadcrumb-item"><a href="{{ route('equipements.show', $equipement) }}">{{ $equipement->code_inventaire }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<form method="POST" action="{{ route('equipements.update', $equipement) }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-pencil me-2 text-warning"></i>Informations générales
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Code inventaire <span class="text-danger">*</span></label>
                            <input type="text" name="code_inventaire" class="form-control @error('code_inventaire') is-invalid @enderror"
                                value="{{ old('code_inventaire', $equipement->code_inventaire) }}" required>
                            @error('code_inventaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Désignation <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control"
                                value="{{ old('designation', $equipement->designation) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marque</label>
                            <input type="text" name="marque" class="form-control" value="{{ old('marque', $equipement->marque) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modèle</label>
                            <input type="text" name="modele" class="form-control" value="{{ old('modele', $equipement->modele) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Numéro de série</label>
                            <input type="text" name="numero_serie" class="form-control @error('numero_serie') is-invalid @enderror"
                                value="{{ old('numero_serie', $equipement->numero_serie) }}">
                            @error('numero_serie')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                            <select name="categorie_id" class="form-select" required>
                                @foreach($categories as $id => $nom)
                                    <option value="{{ $id }}" {{ old('categorie_id', $equipement->categorie_id) == $id ? 'selected' : '' }}>{{ $nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fournisseur</label>
                            <select name="fournisseur_id" class="form-select">
                                <option value="">Aucun</option>
                                @foreach($fournisseurs as $id => $nom)
                                    <option value="{{ $id }}" {{ old('fournisseur_id', $equipement->fournisseur_id) == $id ? 'selected' : '' }}>{{ $nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Service</label>
                            <select name="service_id" class="form-select">
                                <option value="">Non affecté</option>
                                @foreach($services as $id => $nom)
                                    <option value="{{ $id }}" {{ old('service_id', $equipement->service_id) == $id ? 'selected' : '' }}>{{ $nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $equipement->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-box-seam me-2 text-success"></i>Stock & État</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Quantité</label>
                            <input type="number" name="quantite" class="form-control"
                                value="{{ old('quantite', $equipement->quantite) }}" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Seuil alerte</label>
                            <input type="number" name="quantite_min" class="form-control"
                                value="{{ old('quantite_min', $equipement->quantite_min) }}" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">État</label>
                            <select name="etat" class="form-select">
                                @foreach(['operationnel' => 'Opérationnel', 'en_maintenance' => 'En maintenance', 'hors_service' => 'Hors service', 'en_attente' => 'En attente', 'reformé' => 'Réformé'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('etat', $equipement->etat) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Classe de risque</label>
                            <select name="classe_risque" class="form-select">
                                <option value="">Non classifié</option>
                                @foreach(['I', 'IIa', 'IIb', 'III'] as $cl)
                                    <option value="{{ $cl }}" {{ old('classe_risque', $equipement->classe_risque) === $cl ? 'selected' : '' }}>Classe {{ $cl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Image</label>
                            @if($equipement->image)
                                <img src="{{ Storage::url($equipement->image) }}" class="d-block mb-2" style="max-height:80px">
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-calendar3 me-2 text-info"></i>Dates & Finances</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Date d'acquisition</label>
                            <input type="date" name="date_acquisition" class="form-control"
                                value="{{ old('date_acquisition', $equipement->date_acquisition?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fin de garantie</label>
                            <input type="date" name="date_fin_garantie" class="form-control"
                                value="{{ old('date_fin_garantie', $equipement->date_fin_garantie?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Prix d'achat (FCFA)</label>
                            <input type="number" name="prix_achat" class="form-control"
                                value="{{ old('prix_achat', $equipement->prix_achat) }}" step="0.01" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Périodicité maintenance (jours)</label>
                            <input type="number" name="periodicite_maintenance" class="form-control"
                                value="{{ old('periodicite_maintenance', $equipement->periodicite_maintenance) }}" min="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Prochaine maintenance</label>
                            <input type="date" name="prochaine_maintenance" class="form-control"
                                value="{{ old('prochaine_maintenance', $equipement->prochaine_maintenance?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
        <a href="{{ route('equipements.show', $equipement) }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
@endsection
