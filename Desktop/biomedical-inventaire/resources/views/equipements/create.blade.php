@extends('layouts.app')

@section('title', 'Nouvel équipement')
@section('page-title', 'Ajouter un équipement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('equipements.index') }}">Inventaire</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<form method="POST" action="{{ route('equipements.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        {{-- Informations générales --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Informations générales
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Code inventaire <span class="text-danger">*</span></label>
                            <input type="text" name="code_inventaire" class="form-control @error('code_inventaire') is-invalid @enderror"
                                value="{{ old('code_inventaire', $codeGenere) }}" required>
                            @error('code_inventaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Désignation <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
                                value="{{ old('designation') }}" required placeholder="ex: Moniteur multiparamétrique">
                            @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marque</label>
                            <input type="text" name="marque" class="form-control" value="{{ old('marque') }}" placeholder="ex: Philips">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modèle</label>
                            <input type="text" name="modele" class="form-control" value="{{ old('modele') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Numéro de série</label>
                            <input type="text" name="numero_serie" class="form-control @error('numero_serie') is-invalid @enderror"
                                value="{{ old('numero_serie') }}">
                            @error('numero_serie')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                            <select name="categorie_id" class="form-select @error('categorie_id') is-invalid @enderror" required>
                                <option value="">Sélectionner...</option>
                                @foreach($categories as $id => $nom)
                                    <option value="{{ $id }}" {{ old('categorie_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                                @endforeach
                            </select>
                            @error('categorie_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fournisseur</label>
                            <select name="fournisseur_id" class="form-select">
                                <option value="">Aucun</option>
                                @foreach($fournisseurs as $id => $nom)
                                    <option value="{{ $id }}" {{ old('fournisseur_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Service affecté</label>
                            <select name="service_id" class="form-select">
                                <option value="">Non affecté</option>
                                @foreach($services as $id => $nom)
                                    <option value="{{ $id }}" {{ old('service_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description / Notes</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Caractéristiques techniques, remarques...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Données complémentaires --}}
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-box-seam me-2 text-success"></i>Stock & État
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Quantité <span class="text-danger">*</span></label>
                            <input type="number" name="quantite" class="form-control" value="{{ old('quantite', 1) }}" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Seuil alerte</label>
                            <input type="number" name="quantite_min" class="form-control" value="{{ old('quantite_min', 1) }}" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">État <span class="text-danger">*</span></label>
                            <select name="etat" class="form-select" required>
                                <option value="operationnel" {{ old('etat', 'operationnel') === 'operationnel' ? 'selected' : '' }}>Opérationnel</option>
                                <option value="en_attente" {{ old('etat') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                                <option value="en_maintenance" {{ old('etat') === 'en_maintenance' ? 'selected' : '' }}>En maintenance</option>
                                <option value="hors_service" {{ old('etat') === 'hors_service' ? 'selected' : '' }}>Hors service</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Classe de risque (DM)</label>
                            <select name="classe_risque" class="form-select">
                                <option value="">Non classifié</option>
                                <option value="I">Classe I</option>
                                <option value="IIa">Classe IIa</option>
                                <option value="IIb">Classe IIb</option>
                                <option value="III">Classe III</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-calendar3 me-2 text-info"></i>Dates & Finances
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Date d'acquisition</label>
                            <input type="date" name="date_acquisition" class="form-control" value="{{ old('date_acquisition') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fin de garantie</label>
                            <input type="date" name="date_fin_garantie" class="form-control" value="{{ old('date_fin_garantie') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Prix d'achat (FCFA)</label>
                            <input type="number" name="prix_achat" class="form-control" value="{{ old('prix_achat') }}" step="0.01" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Périodicité maintenance (jours)</label>
                            <input type="number" name="periodicite_maintenance" class="form-control"
                                value="{{ old('periodicite_maintenance') }}" min="1" placeholder="ex: 365">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Date expiration (consommables)</label>
                            <input type="date" name="date_expiration" class="form-control" value="{{ old('date_expiration') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
        <a href="{{ route('equipements.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
@endsection
