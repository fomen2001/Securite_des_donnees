@extends('layouts.app')

@section('title', 'Nouveau fournisseur')
@section('page-title', 'Ajouter un fournisseur')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('fournisseurs.index') }}">Fournisseurs</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-truck me-2 text-primary"></i>Informations fournisseur
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('fournisseurs.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                            value="{{ old('nom') }}" required placeholder="ex: Philips Healthcare">
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom du contact</label>
                        <input type="text" name="contact_nom" class="form-control" value="{{ old('contact_nom') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pays</label>
                        <input type="text" name="pays" class="form-control" value="{{ old('pays', 'Cameroun') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adresse</label>
                        <textarea name="adresse" class="form-control" rows="2">{{ old('adresse') }}</textarea>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Site web</label>
                        <input type="url" name="site_web" class="form-control @error('site_web') is-invalid @enderror"
                            value="{{ old('site_web') }}" placeholder="https://...">
                        @error('site_web')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="actif" {{ old('statut', 'actif') === 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Enregistrer
                    </button>
                    <a href="{{ route('fournisseurs.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection
