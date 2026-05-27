@extends('layouts.app')

@section('title', 'Révision salariale')
@section('page-title', 'Révision salariale')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.revisions.index') }}">Révisions</a></li>
    <li class="breadcrumb-item active">Nouvelle révision</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-graph-up-arrow me-2"></i>Révision salariale</div>
    <div class="card-body">
        <form action="{{ route('rh.revisions.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Employé <span class="text-danger">*</span></label>
                <select name="employe_id" id="employe_id" class="form-select @error('employe_id') is-invalid @enderror" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}"
                            data-salaire="{{ $e->salaire_base }}"
                            @selected(old('employe_id', $employe?->id) == $e->id)>
                            {{ $e->matricule }} – {{ $e->nom_complet }} ({{ $e->poste }})
                        </option>
                    @endforeach
                </select>
                @error('employe_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Salaire actuel</label>
                <div class="input-group">
                    <input type="text" id="salaire_actuel" class="form-control bg-light" readonly placeholder="Sélectionnez un employé">
                    <span class="input-group-text">FCFA</span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nouveau salaire (FCFA) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="nouveau_salaire" id="nouveau_salaire"
                           value="{{ old('nouveau_salaire') }}" min="41875" step="500"
                           class="form-control @error('nouveau_salaire') is-invalid @enderror" required
                           oninput="calculerVariation()">
                    <span class="input-group-text">FCFA</span>
                </div>
                <div id="info-variation" class="mt-1 small"></div>
                @error('nouveau_salaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">SMIG minimum : 41 875 FCFA/mois</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Date d'effet <span class="text-danger">*</span></label>
                <input type="date" name="date_effet" value="{{ old('date_effet', now()->format('Y-m-d')) }}" class="form-control" required>
                <small class="text-muted">Si la date est aujourd'hui ou passée, le salaire est mis à jour immédiatement.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Motif <span class="text-danger">*</span></label>
                <select name="motif" class="form-select @error('motif') is-invalid @enderror" required>
                    <option value="revision_annuelle" @selected(old('motif','revision_annuelle')=='revision_annuelle')>Révision annuelle</option>
                    <option value="augmentation_merite" @selected(old('motif')=='augmentation_merite')>Augmentation au mérite</option>
                    <option value="promotion" @selected(old('motif')=='promotion')>Promotion</option>
                    <option value="reclassement" @selected(old('motif')=='reclassement')>Reclassement</option>
                    <option value="anciennete" @selected(old('motif')=='anciennete')>Ancienneté</option>
                    <option value="autre" @selected(old('motif')=='autre')>Autre</option>
                </select>
                @error('motif')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Commentaire</label>
                <textarea name="commentaire" class="form-control" rows="2" placeholder="Justification de la révision...">{{ old('commentaire') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                <a href="{{ route('rh.revisions.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('employe_id').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const sal = parseFloat(opt.dataset.salaire) || 0;
    document.getElementById('salaire_actuel').value = sal > 0 ? sal.toLocaleString('fr-FR') : '';
    calculerVariation();
});

function calculerVariation() {
    const sel = document.getElementById('employe_id');
    const opt = sel.options[sel.selectedIndex];
    const ancien = parseFloat(opt.dataset.salaire) || 0;
    const nouveau = parseFloat(document.getElementById('nouveau_salaire').value) || 0;
    const el = document.getElementById('info-variation');

    if (ancien > 0 && nouveau > 0) {
        const diff = nouveau - ancien;
        const pct  = (diff / ancien) * 100;
        const sign = diff >= 0 ? '+' : '';
        el.textContent = `${sign}${diff.toLocaleString('fr-FR')} FCFA (${sign}${pct.toFixed(1)} %)`;
        el.className = `mt-1 small fw-semibold ${diff > 0 ? 'text-success' : (diff < 0 ? 'text-danger' : 'text-muted')}`;
    } else {
        el.textContent = '';
    }
}
</script>
@endpush
