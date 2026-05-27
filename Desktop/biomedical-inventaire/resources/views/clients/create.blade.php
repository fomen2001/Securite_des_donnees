@extends('layouts.app')

@section('title', 'Nouveau client')
@section('page-title', 'Ajouter un client')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<form method="POST" action="{{ route('clients.store') }}">
    @csrf
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-person-plus me-2 text-primary"></i>Informations client
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Code client <span class="text-danger">*</span></label>
                    <input type="text" name="code_client" class="form-control @error('code_client') is-invalid @enderror"
                        value="{{ old('code_client', $code) }}" required>
                    @error('code_client')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                        value="{{ old('nom') }}" required placeholder="ex: Clinique La Grâce">
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        @foreach(['hopital' => 'Hôpital', 'clinique' => 'Clinique', 'cabinet' => 'Cabinet médical', 'laboratoire' => 'Laboratoire', 'particulier' => 'Particulier', 'autre' => 'Autre'] as $v => $l)
                            <option value="{{ $v }}" {{ old('type', 'hopital') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">N° Contribuable (NIU)</label>
                    <input type="text" name="numero_contribuable" class="form-control" value="{{ old('numero_contribuable') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom du contact</label>
                    <input type="text" name="contact_nom" class="form-control" value="{{ old('contact_nom') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}" placeholder="6XX XXX XXX">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control" value="{{ old('ville', 'Yaoundé') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pays</label>
                    <input type="text" name="pays" class="form-control" value="{{ old('pays', 'Cameroun') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="actif" selected>Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" class="form-control" rows="2">{{ old('adresse') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</form>
</div>
</div>
@endsection
