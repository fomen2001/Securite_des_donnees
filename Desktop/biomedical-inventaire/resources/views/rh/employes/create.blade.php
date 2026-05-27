@extends('layouts.app')

@section('title', 'Nouvel employé')
@section('page-title', 'Enregistrer un employé')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.employes.index') }}">Employés</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<form action="{{ route('rh.employes.store') }}" method="POST">
    @csrf

    <div class="row g-4">

        {{-- Informations personnelles --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold"><i class="bi bi-person me-2"></i>Informations personnelles</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom') }}" class="form-control @error('nom') is-invalid @enderror" required>
                            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" value="{{ old('prenom') }}" class="form-control @error('prenom') is-invalid @enderror" required>
                            @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date de naissance</label>
                            <input type="date" name="date_naissance" value="{{ old('date_naissance') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Lieu de naissance</label>
                            <input type="text" name="lieu_naissance" value="{{ old('lieu_naissance') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Sexe <span class="text-danger">*</span></label>
                            <select name="sexe" class="form-select" required>
                                <option value="M" @selected(old('sexe','M')=='M')>Masculin</option>
                                <option value="F" @selected(old('sexe')=='F')>Féminin</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Nationalité</label>
                            <input type="text" name="nationalite" value="{{ old('nationalite', 'Camerounaise') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Situation matrimoniale <span class="text-danger">*</span></label>
                            <select name="situation_matrimoniale" class="form-select" required>
                                <option value="celibataire" @selected(old('situation_matrimoniale','celibataire')=='celibataire')>Célibataire</option>
                                <option value="marie" @selected(old('situation_matrimoniale')=='marie')>Marié(e)</option>
                                <option value="divorce" @selected(old('situation_matrimoniale')=='divorce')>Divorcé(e)</option>
                                <option value="veuf" @selected(old('situation_matrimoniale')=='veuf')>Veuf/Veuve</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Nombre d'enfants</label>
                            <input type="number" name="nombre_enfants" value="{{ old('nombre_enfants', 0) }}" min="0" max="20" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" value="{{ old('telephone') }}" class="form-control" placeholder="6XXXXXXXX">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" value="{{ old('adresse') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Ville</label>
                            <input type="text" name="ville" value="{{ old('ville', 'Yaoundé') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">N° CNI</label>
                            <input type="text" name="numero_cni" value="{{ old('numero_cni') }}" class="form-control @error('numero_cni') is-invalid @enderror">
                            @error('numero_cni')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informations professionnelles --}}
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header fw-semibold"><i class="bi bi-briefcase me-2"></i>Informations professionnelles</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Matricule <span class="text-danger">*</span></label>
                            <input type="text" name="matricule" value="{{ old('matricule', $matricule) }}" class="form-control @error('matricule') is-invalid @enderror" required>
                            @error('matricule')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date d'embauche <span class="text-danger">*</span></label>
                            <input type="date" name="date_embauche" value="{{ old('date_embauche') }}" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Poste <span class="text-danger">*</span></label>
                            <input type="text" name="poste" value="{{ old('poste') }}" class="form-control" required placeholder="Ex. Technicien Biomédical">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Département</label>
                            <input type="text" name="departement" value="{{ old('departement') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Service</label>
                            <select name="service_id" class="form-select">
                                <option value="">— Aucun —</option>
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}" @selected(old('service_id') == $s->id)>{{ $s->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Type de contrat <span class="text-danger">*</span></label>
                            <select name="type_contrat" class="form-select" required>
                                @foreach(['CDI'=>'CDI – Durée indéterminée','CDD'=>'CDD – Durée déterminée','stage'=>'Stage','consultant'=>'Consultant'] as $v => $l)
                                    <option value="{{ $v }}" @selected(old('type_contrat','CDI') == $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Fin de contrat</label>
                            <input type="date" name="date_fin_contrat" value="{{ old('date_fin_contrat') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Catégorie professionnelle</label>
                            <input type="text" name="categorie_professionnelle" value="{{ old('categorie_professionnelle') }}" class="form-control" placeholder="Ex. A1, B2">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Statut</label>
                            <select name="statut" class="form-select">
                                <option value="actif" @selected(old('statut','actif')=='actif')>Actif</option>
                                <option value="suspendu" @selected(old('statut')=='suspendu')>Suspendu</option>
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
                                <input type="number" name="salaire_base" value="{{ old('salaire_base') }}" class="form-control @error('salaire_base') is-invalid @enderror" required min="41875" step="500">
                                <span class="input-group-text">FCFA</span>
                            </div>
                            <small class="text-muted">SMIG : 41 875 FCFA/mois minimum</small>
                            @error('salaire_base')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">N° CNPS</label>
                            <input type="text" name="numero_cnps" value="{{ old('numero_cnps') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">N° Contribuable</label>
                            <input type="text" name="numero_contribuable" value="{{ old('numero_contribuable') }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Enregistrer l'employé
        </button>
        <a href="{{ route('rh.employes.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
@endsection
