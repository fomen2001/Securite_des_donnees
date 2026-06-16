@extends('layouts.app')

@section('title', 'Modifier — ' . $document->titre)
@section('page-title', 'Modifier le document')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
    <li class="breadcrumb-item"><a href="{{ route('documents.show', $document) }}">{{ $document->reference }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

<form action="{{ route('documents.update', $document) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-pencil me-2"></i>Informations du document</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                <input type="text" name="titre" value="{{ old('titre', $document->titre) }}"
                       class="form-control @error('titre') is-invalid @enderror" required>
                @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
                <select name="document_categorie_id" class="form-select" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('document_categorie_id', $document->document_categorie_id) == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nom }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Type</label>
                <select name="type" class="form-select" required>
                    @foreach(['contrat'=>'Contrat','attestation'=>'Attestation','facture'=>'Facture','licence'=>'Licence','rapport'=>'Rapport','proces_verbal'=>'Procès-verbal','convention'=>'Convention','autre'=>'Autre'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('type', $document->type) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Date du document</label>
                <input type="date" name="date_document" value="{{ old('date_document', $document->date_document->format('Y-m-d')) }}"
                       class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Date d'expiration</label>
                <input type="date" name="date_expiration"
                       value="{{ old('date_expiration', $document->date_expiration?->format('Y-m-d')) }}"
                       class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Confidentialité</label>
                <select name="confidentialite" class="form-select" required>
                    @foreach(['interne'=>'Interne','public'=>'Public','confidentiel'=>'Confidentiel'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('confidentialite', $document->confidentialite) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Statut</label>
                <select name="statut" class="form-select">
                    @foreach(['actif'=>'Actif','archive'=>'Archivé','expire'=>'Expiré'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('statut', $document->statut) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Mots-clés / Tags</label>
                <input type="text" name="tags" value="{{ old('tags', $document->tags) }}" class="form-control">
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $document->description) }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- Fichier actuel + remplacement --}}
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-file-earmark me-2"></i>Fichier</div>
    <div class="card-body">
        <div class="alert alert-light border d-flex align-items-center gap-3 mb-3">
            <i class="bi {{ $document->icone_mime }} fs-3"></i>
            <div>
                <div class="fw-semibold">{{ $document->fichier_nom_original }}</div>
                <div class="text-muted small">{{ $document->taille_lisible }}</div>
            </div>
        </div>
        <label class="form-label">Remplacer le fichier (optionnel)</label>
        <input type="file" name="fichier" class="form-control"
               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip">
        <small class="text-muted">Laisser vide pour conserver le fichier actuel. Max 20 Mo.</small>
        @error('fichier')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">Annuler</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Enregistrer</button>
</div>

</form>
</div>
</div>
@endsection
