@extends('layouts.app')

@section('title', 'Ajouter un document')
@section('page-title', 'Ajouter un document')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

<form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2"></i>Informations du document</div>
    <div class="card-body">

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                <input type="text" name="titre" value="{{ old('titre') }}"
                       class="form-control @error('titre') is-invalid @enderror"
                       placeholder="Ex : Statuts de la société, Contrat de bail, Agrément DGS..." required>
                @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
                <select name="document_categorie_id" class="form-select @error('document_categorie_id') is-invalid @enderror" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('document_categorie_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nom }}
                    </option>
                    @endforeach
                </select>
                @error('document_categorie_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Type de document <span class="text-danger">*</span></label>
                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                    <option value="">— Sélectionner —</option>
                    @foreach(['contrat'=>'Contrat','attestation'=>'Attestation','facture'=>'Facture','licence'=>'Licence','rapport'=>'Rapport','proces_verbal'=>'Procès-verbal','convention'=>'Convention','autre'=>'Autre'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Date du document <span class="text-danger">*</span></label>
                <input type="date" name="date_document" value="{{ old('date_document', now()->format('Y-m-d')) }}"
                       class="form-control @error('date_document') is-invalid @enderror" required>
                @error('date_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Date d'expiration</label>
                <input type="date" name="date_expiration" value="{{ old('date_expiration') }}"
                       class="form-control @error('date_expiration') is-invalid @enderror">
                <small class="text-muted">Laisser vide si le document n'expire pas.</small>
                @error('date_expiration')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Confidentialité <span class="text-danger">*</span></label>
                <select name="confidentialite" class="form-select @error('confidentialite') is-invalid @enderror" required>
                    <option value="interne"      {{ old('confidentialite', 'interne') === 'interne'      ? 'selected' : '' }}>
                        🏢 Interne — visible par les employés
                    </option>
                    <option value="public"       {{ old('confidentialite') === 'public'       ? 'selected' : '' }}>
                        🌍 Public — accessible à tous
                    </option>
                    <option value="confidentiel" {{ old('confidentialite') === 'confidentiel' ? 'selected' : '' }}>
                        🔒 Confidentiel — accès restreint
                    </option>
                </select>
                @error('confidentialite')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Mots-clés / Tags</label>
                <input type="text" name="tags" value="{{ old('tags') }}"
                       class="form-control" placeholder="Ex: agrément, santé, 2025 (séparés par des virgules)">
                <small class="text-muted">Facilitent la recherche.</small>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Contexte, numéro de référence externe, entité concernée...">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- Zone upload --}}
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-upload me-2"></i>Fichier <span class="text-danger">*</span></div>
    <div class="card-body">
        <div id="drop-zone" class="border-2 border-dashed rounded-3 p-5 text-center bg-light"
             style="border-style:dashed !important; cursor:pointer">
            <i class="bi bi-cloud-upload fs-1 text-muted d-block mb-2"></i>
            <div class="fw-semibold mb-1">Glissez-déposez votre fichier ici</div>
            <div class="text-muted small mb-3">ou cliquez pour sélectionner</div>
            <input type="file" name="fichier" id="inp-fichier" class="d-none @error('fichier') is-invalid @enderror"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip" required>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('inp-fichier').click()">
                <i class="bi bi-folder2-open me-1"></i>Parcourir
            </button>
            <div id="fichier-info" class="mt-3 d-none">
                <div class="badge bg-success fs-6 p-2" id="fichier-nom"></div>
            </div>
        </div>
        <div class="text-muted small mt-2">
            Formats acceptés : PDF, Word, Excel, PowerPoint, Images (JPG, PNG), ZIP — Max 20 Mo
        </div>
        @error('fichier')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">Annuler</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i>Enregistrer le document
    </button>
</div>

</form>
</div>
</div>
@endsection

@push('scripts')
<script>
const inp = document.getElementById('inp-fichier');
const zone = document.getElementById('drop-zone');

inp.addEventListener('change', () => afficherFichier(inp.files[0]));

zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('border-primary'); });
zone.addEventListener('dragleave', ()  => zone.classList.remove('border-primary'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('border-primary');
    if (e.dataTransfer.files.length) {
        const dt = new DataTransfer();
        dt.items.add(e.dataTransfer.files[0]);
        inp.files = dt.files;
        afficherFichier(e.dataTransfer.files[0]);
    }
});

function afficherFichier(file) {
    if (!file) return;
    document.getElementById('fichier-nom').textContent = file.name + ' (' + (file.size / 1048576).toFixed(1) + ' Mo)';
    document.getElementById('fichier-info').classList.remove('d-none');
}
</script>
@endpush
