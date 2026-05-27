@extends('layouts.app')

@section('title', 'Modifier — ' . $employe->nom_complet)
@section('page-title', 'Modifier l\'employé')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.employes.index') }}">Employés</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.employes.show', $employe) }}">{{ $employe->matricule }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<form action="{{ route('rh.employes.update', $employe) }}" method="POST">
    @csrf @method('PUT')

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold"><i class="bi bi-person me-2"></i>Informations personnelles</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom', $employe->nom) }}" class="form-control @error('nom') is-invalid @enderror" required>
                            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" value="{{ old('prenom', $employe->prenom) }}" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date de naissance</label>
                            <input type="date" name="date_naissance" value="{{ old('date_naissance', $employe->date_naissance?->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Lieu de naissance</label>
                            <input type="text" name="lieu_naissance" value="{{ old('lieu_naissance', $employe->lieu_naissance) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Sexe</label>
                            <select name="sexe" class="form-select">
                                <option value="M" @selected(old('sexe', $employe->sexe)=='M')>Masculin</option>
                                <option value="F" @selected(old('sexe', $employe->sexe)=='F')>Féminin</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Nationalité</label>
                            <input type="text" name="nationalite" value="{{ old('nationalite', $employe->nationalite) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Situation matrimoniale</label>
                            <select name="situation_matrimoniale" class="form-select">
                                @foreach(['celibataire'=>'Célibataire','marie'=>'Marié(e)','divorce'=>'Divorcé(e)','veuf'=>'Veuf/Veuve'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old('situation_matrimoniale', $employe->situation_matrimoniale)==$v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Nombre d'enfants</label>
                            <input type="number" name="nombre_enfants" value="{{ old('nombre_enfants', $employe->nombre_enfants) }}" min="0" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" value="{{ old('telephone', $employe->telephone) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $employe->email) }}" class="form-control @error('email') is-invalid @enderror">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" value="{{ old('adresse', $employe->adresse) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Ville</label>
                            <input type="text" name="ville" value="{{ old('ville', $employe->ville) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">N° CNI</label>
                            <input type="text" name="numero_cni" value="{{ old('numero_cni', $employe->numero_cni) }}" class="form-control @error('numero_cni') is-invalid @enderror">
                            @error('numero_cni')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header fw-semibold"><i class="bi bi-briefcase me-2"></i>Informations professionnelles</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Matricule</label>
                            <input type="text" name="matricule" value="{{ old('matricule', $employe->matricule) }}" class="form-control @error('matricule') is-invalid @enderror" required>
                            @error('matricule')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date d'embauche</label>
                            <input type="date" name="date_embauche" value="{{ old('date_embauche', $employe->date_embauche->format('Y-m-d')) }}" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Poste</label>
                            <input type="text" name="poste" value="{{ old('poste', $employe->poste) }}" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Département</label>
                            <input type="text" name="departement" value="{{ old('departement', $employe->departement) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Service</label>
                            <select name="service_id" class="form-select">
                                <option value="">— Aucun —</option>
                                @foreach($services as $s)
                                <option value="{{ $s->id }}" @selected(old('service_id', $employe->service_id) == $s->id)>{{ $s->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Type de contrat</label>
                            <select name="type_contrat" class="form-select">
                                @foreach(['CDI','CDD','stage','consultant'] as $c)
                                <option value="{{ $c }}" @selected(old('type_contrat', $employe->type_contrat)==$c)>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Fin de contrat</label>
                            <input type="date" name="date_fin_contrat" value="{{ old('date_fin_contrat', $employe->date_fin_contrat?->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Catégorie professionnelle</label>
                            <input type="text" name="categorie_professionnelle" value="{{ old('categorie_professionnelle', $employe->categorie_professionnelle) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Statut</label>
                            <select name="statut" class="form-select">
                                @foreach(['actif'=>'Actif','conge'=>'En congé','suspendu'=>'Suspendu','demissionne'=>'Démissionné','licencie'=>'Licencié'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old('statut', $employe->statut)==$v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold"><i class="bi bi-cash-stack me-2"></i>Rémunération & Identifiants</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Salaire de base (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="salaire_base" value="{{ old('salaire_base', $employe->salaire_base) }}" class="form-control" required min="41875" step="500">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Solde congé (jours)</label>
                            <input type="number" name="solde_conge" value="{{ old('solde_conge', $employe->solde_conge) }}" class="form-control" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">N° CNPS</label>
                            <input type="text" name="numero_cnps" value="{{ old('numero_cnps', $employe->numero_cnps) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">N° Contribuable</label>
                            <input type="text" name="numero_contribuable" value="{{ old('numero_contribuable', $employe->numero_contribuable) }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $employe->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
        <a href="{{ route('rh.employes.show', $employe) }}" class="btn btn-outline-secondary">Annuler</a>
        @can('rh.employes.supprimer')
        <form action="{{ route('rh.employes.destroy', $employe) }}" method="POST" class="ms-auto"
              onsubmit="return confirm('Archiver {{ $employe->nom_complet }} ?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-archive me-1"></i>Archiver</button>
        </form>
        @endcan
    </div>
</form>
@endsection
