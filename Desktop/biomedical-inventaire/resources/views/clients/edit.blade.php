@extends('layouts.app')

@section('title', 'Modifier ' . $client->nom)
@section('page-title', 'Modifier le client')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<form method="POST" action="{{ route('clients.update', $client) }}">
    @csrf @method('PUT')
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-pencil me-2 text-warning"></i>{{ $client->nom }}
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Code client</label>
                    <input type="text" name="code_client" class="form-control"
                        value="{{ old('code_client', $client->code_client) }}" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control"
                        value="{{ old('nom', $client->nom) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        @foreach(['hopital' => 'Hôpital', 'clinique' => 'Clinique', 'cabinet' => 'Cabinet médical', 'laboratoire' => 'Laboratoire', 'particulier' => 'Particulier', 'autre' => 'Autre'] as $v => $l)
                            <option value="{{ $v }}" {{ old('type', $client->type) === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">N° Contribuable</label>
                    <input type="text" name="numero_contribuable" class="form-control"
                        value="{{ old('numero_contribuable', $client->numero_contribuable) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact</label>
                    <input type="text" name="contact_nom" class="form-control"
                        value="{{ old('contact_nom', $client->contact_nom) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control"
                        value="{{ old('telephone', $client->telephone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                        value="{{ old('email', $client->email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control"
                        value="{{ old('ville', $client->ville) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pays</label>
                    <input type="text" name="pays" class="form-control"
                        value="{{ old('pays', $client->pays) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="actif" {{ old('statut', $client->statut) === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ old('statut', $client->statut) === 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" class="form-control" rows="2">{{ old('adresse', $client->adresse) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $client->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</form>
</div>
</div>
@endsection
