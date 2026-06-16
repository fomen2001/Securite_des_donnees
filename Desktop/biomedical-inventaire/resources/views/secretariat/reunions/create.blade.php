@extends('layouts.app')
@section('title', 'Nouveau rapport de réunion')
@section('page-title', 'Rédiger un rapport de réunion')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('secretariat.reunions.index') }}">Réunions</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<form action="{{ route('secretariat.reunions.store') }}" method="POST" id="form-reunion">
@csrf

<div class="row g-4">
<div class="col-lg-8">

{{-- Informations générales --}}
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations générales</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Titre de la réunion <span class="text-danger">*</span></label>
                <input type="text" name="titre" value="{{ old('titre') }}"
                       class="form-control @error('titre') is-invalid @enderror"
                       placeholder="Ex: Réunion de suivi projet X, Bilan trimestriel…" required>
                @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Date & heure <span class="text-danger">*</span></label>
                <input type="datetime-local" name="date_reunion"
                       value="{{ old('date_reunion', now()->format('Y-m-d\TH:i')) }}"
                       class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Lieu</label>
                <input type="text" name="lieu" value="{{ old('lieu') }}"
                       class="form-control" placeholder="Ex: Salle de conférence, En ligne…">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Type</label>
                <select name="type" class="form-select">
                    @foreach(['interne'=>'Réunion interne','client'=>'Avec client','fournisseur'=>'Avec fournisseur','partenaire'=>'Avec partenaire','autre'=>'Autre'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('type','interne')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Ordre du jour --}}
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-list-ol me-2 text-warning"></i>Ordre du jour</div>
    <div class="card-body">
        <textarea name="ordre_du_jour" class="form-control" rows="4"
                  placeholder="1. Point sur les ventes du mois&#10;2. Revue des objectifs&#10;3. Questions diverses…">{{ old('ordre_du_jour') }}</textarea>
    </div>
</div>

{{-- Compte-rendu --}}
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-journal-text me-2 text-success"></i>Compte-rendu <span class="text-danger">*</span></div>
    <div class="card-body">
        <textarea name="compte_rendu" class="form-control @error('compte_rendu') is-invalid @enderror"
                  rows="8" placeholder="Résumé des discussions…" required>{{ old('compte_rendu') }}</textarea>
        @error('compte_rendu')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

{{-- Décisions --}}
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-check2-square me-2 text-primary"></i>Décisions prises</div>
    <div class="card-body">
        <textarea name="decisions" class="form-control" rows="4"
                  placeholder="- Décision 1&#10;- Décision 2…">{{ old('decisions') }}</textarea>
    </div>
</div>

{{-- Actions à suivre --}}
<div class="card mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-arrow-right-circle me-2 text-danger"></i>Actions à suivre</div>
    <div class="card-body">
        <textarea name="actions_a_suivre" class="form-control" rows="4"
                  placeholder="- [Responsable] Action à mener avant le JJ/MM&#10;- …">{{ old('actions_a_suivre') }}</textarea>
    </div>
</div>

</div>

{{-- Participants + Statut --}}
<div class="col-lg-4">

    <div class="card mb-4">
        <div class="card-header fw-semibold"><i class="bi bi-people me-2"></i>Participants</div>
        <div class="card-body">
            <div id="participants-list">
                <div class="participant-row mb-2 p-2 border rounded bg-light">
                    <div class="row g-1">
                        <div class="col-12">
                            <input type="text" name="participants[0][nom]" class="form-control form-control-sm"
                                   placeholder="Nom complet *" required>
                        </div>
                        <div class="col-7">
                            <input type="text" name="participants[0][fonction]" class="form-control form-control-sm"
                                   placeholder="Fonction">
                        </div>
                        <div class="col-5">
                            <input type="text" name="participants[0][entreprise]" class="form-control form-control-sm"
                                   placeholder="Entreprise">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-check-sm">
                                <input class="form-check-input" type="checkbox" name="participants[0][present]"
                                       checked id="pres_0">
                                <label class="form-check-label small" for="pres_0">Présent(e)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2" onclick="ajouterParticipant()">
                <i class="bi bi-plus-circle me-1"></i>Ajouter un participant
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">Statut du rapport</div>
        <div class="card-body">
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="statut" value="brouillon"
                       id="st_brouillon" {{ old('statut','brouillon')==='brouillon'?'checked':'' }}>
                <label class="form-check-label" for="st_brouillon">
                    <span class="badge bg-secondary me-1">Brouillon</span> — en cours de rédaction
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statut" value="finalise"
                       id="st_finalise" {{ old('statut')==='finalise'?'checked':'' }}>
                <label class="form-check-label" for="st_finalise">
                    <span class="badge bg-success me-1">Finalisé</span> — rapport validé
                </label>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Enregistrer le rapport
        </button>
        <a href="{{ route('secretariat.reunions.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>

</div>
</div>
</form>
@endsection

@push('scripts')
<script>
let idx = 1;
function ajouterParticipant() {
    const tpl = `
    <div class="participant-row mb-2 p-2 border rounded bg-light">
        <div class="row g-1">
            <div class="col-11">
                <input type="text" name="participants[${idx}][nom]" class="form-control form-control-sm" placeholder="Nom complet">
            </div>
            <div class="col-1 d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="this.closest('.participant-row').remove()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="col-7">
                <input type="text" name="participants[${idx}][fonction]" class="form-control form-control-sm" placeholder="Fonction">
            </div>
            <div class="col-5">
                <input type="text" name="participants[${idx}][entreprise]" class="form-control form-control-sm" placeholder="Entreprise">
            </div>
            <div class="col-12">
                <div class="form-check form-check-sm">
                    <input class="form-check-input" type="checkbox" name="participants[${idx}][present]" checked id="pres_${idx}">
                    <label class="form-check-label small" for="pres_${idx}">Présent(e)</label>
                </div>
            </div>
        </div>
    </div>`;
    document.getElementById('participants-list').insertAdjacentHTML('beforeend', tpl);
    idx++;
}
</script>
@endpush
@push('styles')
<style>.btn-xs{padding:.2rem .5rem;font-size:.75rem;}</style>
@endpush
