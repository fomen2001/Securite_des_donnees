@extends('layouts.app')

@section('title', 'Modifier ' . $fournisseur->nom)
@section('page-title', 'Modifier le fournisseur')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('fournisseurs.index') }}">Fournisseurs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('fournisseurs.show', $fournisseur) }}">{{ $fournisseur->nom }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-pencil me-2 text-warning"></i>{{ $fournisseur->nom }}
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('fournisseurs.update', $fournisseur) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                            value="{{ old('nom', $fournisseur->nom) }}" required>
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom du contact</label>
                        <input type="text" name="contact_nom" class="form-control"
                            value="{{ old('contact_nom', $fournisseur->contact_nom) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control"
                            value="{{ old('telephone', $fournisseur->telephone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', $fournisseur->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pays</label>
                        <input type="text" name="pays" class="form-control"
                            value="{{ old('pays', $fournisseur->pays) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adresse</label>
                        <textarea name="adresse" class="form-control" rows="2">{{ old('adresse', $fournisseur->adresse) }}</textarea>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Site web</label>
                        <input type="url" name="site_web" class="form-control"
                            value="{{ old('site_web', $fournisseur->site_web) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="actif" {{ old('statut', $fournisseur->statut) === 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('statut', $fournisseur->statut) === 'inactif' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Enregistrer
                    </button>
                    <a href="{{ route('fournisseurs.show', $fournisseur) }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
