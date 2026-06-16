@extends('layouts.app')
@section('title', 'Modifier — ' . $reunion->titre)
@section('page-title', 'Modifier le rapport')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('secretariat.reunions.index') }}">Réunions</a></li>
    <li class="breadcrumb-item"><a href="{{ route('secretariat.reunions.show', $reunion) }}">{{ $reunion->reference }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<form action="{{ route('secretariat.reunions.update', $reunion) }}" method="POST">
@csrf @method('PUT')

<div class="row g-4">
<div class="col-lg-8">

<div class="card mb-4">
    <div class="card-header fw-semibold">Informations générales</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                <input type="text" name="titre" value="{{ old('titre', $reunion->titre) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Date & heure</label>
                <input type="datetime-local" name="date_reunion"
                       value="{{ old('date_reunion', $reunion->date_reunion->format('Y-m-d\TH:i')) }}"
                       class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Lieu</label>
                <input type="text" name="lieu" value="{{ old('lieu', $reunion->lieu) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Type</label>
                <select name="type" class="form-select">
                    @foreach(['interne'=>'Réunion interne','client'=>'Avec client','fournisseur'=>'Avec fournisseur','partenaire'=>'Avec partenaire','autre'=>'Autre'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('type',$reunion->type)===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header fw-semibold">Ordre du jour</div>
    <div class="card-body">
        <textarea name="ordre_du_jour" class="form-control" rows="4">{{ old('ordre_du_jour', $reunion->ordre_du_jour) }}</textarea>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header fw-semibold">Compte-rendu <span class="text-danger">*</span></div>
    <div class="card-body">
        <textarea name="compte_rendu" class="form-control" rows="8" required>{{ old('compte_rendu', $reunion->compte_rendu) }}</textarea>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header fw-semibold">Décisions</div>
    <div class="card-body">
        <textarea name="decisions" class="form-control" rows="4">{{ old('decisions', $reunion->decisions) }}</textarea>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header fw-semibold">Actions à suivre</div>
    <div class="card-body">
        <textarea name="actions_a_suivre" class="form-control" rows="4">{{ old('actions_a_suivre', $reunion->actions_a_suivre) }}</textarea>
    </div>
</div>

</div>

<div class="col-lg-4">

    <div class="card mb-4">
        <div class="card-header fw-semibold">Participants</div>
        <div class="card-body">
            <div id="participants-list">
                @foreach($reunion->participants as $i => $p)
                <div class="participant-row mb-2 p-2 border rounded bg-light">
                    <div class="row g-1">
                        <div class="col-11">
                            <input type="text" name="participants[{{ $i }}][nom]"
                                   value="{{ $p->nom }}" class="form-control form-control-sm" placeholder="Nom complet">
                        </div>
                        <div class="col-1 d-flex align-items-center justify-content-end">
                            <button type="button" class="btn btn-xs btn-outline-danger"
                                    onclick="this.closest('.participant-row').remove()">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        <div class="col-7">
                            <input type="text" name="participants[{{ $i }}][fonction]"
                                   value="{{ $p->fonction }}" class="form-control form-control-sm" placeholder="Fonction">
                        </div>
                        <div class="col-5">
                            <input type="text" name="participants[{{ $i }}][entreprise]"
                                   value="{{ $p->entreprise }}" class="form-control form-control-sm" placeholder="Entreprise">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-check-sm">
                                <input class="form-check-input" type="checkbox" name="participants[{{ $i }}][present]"
                                       {{ $p->present ? 'checked' : '' }} id="pres_{{ $i }}">
                                <label class="form-check-label small" for="pres_{{ $i }}">Présent(e)</label>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2" onclick="ajouterParticipant()">
                <i class="bi bi-plus-circle me-1"></i>Ajouter
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">Statut</div>
        <div class="card-body">
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="statut" value="brouillon"
                       {{ old('statut',$reunion->statut)==='brouillon'?'checked':'' }} id="st_b">
                <label class="form-check-label" for="st_b"><span class="badge bg-secondary">Brouillon</span></label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="statut" value="finalise"
                       {{ old('statut',$reunion->statut)==='finalise'?'checked':'' }} id="st_f">
                <label class="form-check-label" for="st_f"><span class="badge bg-success">Finalisé</span></label>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Enregistrer</button>
        <a href="{{ route('secretariat.reunions.show', $reunion) }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</div>
</div>
</form>
@endsection

@push('scripts')
<script>
let idx = {{ $reunion->participants->count() }};
function ajouterParticipant() {
    const tpl = `<div class="participant-row mb-2 p-2 border rounded bg-light">
        <div class="row g-1">
            <div class="col-11"><input type="text" name="participants[${idx}][nom]" class="form-control form-control-sm" placeholder="Nom complet"></div>
            <div class="col-1 d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="this.closest('.participant-row').remove()"><i class="bi bi-x"></i></button>
            </div>
            <div class="col-7"><input type="text" name="participants[${idx}][fonction]" class="form-control form-control-sm" placeholder="Fonction"></div>
            <div class="col-5"><input type="text" name="participants[${idx}][entreprise]" class="form-control form-control-sm" placeholder="Entreprise"></div>
            <div class="col-12"><div class="form-check form-check-sm">
                <input class="form-check-input" type="checkbox" name="participants[${idx}][present]" checked id="pres_${idx}">
                <label class="form-check-label small" for="pres_${idx}">Présent(e)</label>
            </div></div>
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
