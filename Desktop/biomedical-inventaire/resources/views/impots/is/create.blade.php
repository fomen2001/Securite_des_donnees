@extends('layouts.app')

@section('title', 'Déclaration IS')
@section('page-title', 'Nouvelle déclaration IS')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item"><a href="{{ route('impots.is.index') }}">IS</a></li>
    <li class="breadcrumb-item active">Nouvelle déclaration</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-bank me-2"></i>Déclaration IS</div>
    <div class="card-body">
        <form action="{{ route('impots.is.store') }}" method="POST" id="form-is">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                    <select name="type" id="sel-type" class="form-select" required onchange="toggleType()">
                        <option value="acompte" @selected($type === 'acompte')>Acompte provisionnel</option>
                        <option value="annuelle" @selected($type === 'annuelle')>Déclaration annuelle</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Année fiscale <span class="text-danger">*</span></label>
                    <input type="number" name="annee" id="inp-annee" value="{{ old('annee', $annee) }}"
                           min="2020" class="form-control" required>
                </div>
                <div class="col-md-4" id="bloc-trimestre">
                    <label class="form-label fw-semibold">Trimestre</label>
                    <select name="trimestre" class="form-select">
                        @foreach([1=>'T1 — 15 Fév',2=>'T2 — 15 Mai',3=>'T3 — 15 Août',4=>'T4 — 15 Nov'] as $t => $l)
                            <option value="{{ $t }}" @selected($trim == $t)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr>

            <h6 class="fw-semibold mb-3">Base de calcul</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Chiffre d'affaires HT (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" name="chiffre_affaires" id="inp-ca" step="1" min="0"
                           value="{{ old('chiffre_affaires', $donneesAnnuelles['ca']) }}"
                           class="form-control @error('chiffre_affaires') is-invalid @enderror"
                           required oninput="calculerIS()">
                    @error('chiffre_affaires')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Pré-rempli depuis les ventes de l'année</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bénéfice imposable (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" name="benefice_imposable" id="inp-benefice" step="1"
                           value="{{ old('benefice_imposable', $donneesAnnuelles['ca'] - $donneesAnnuelles['charges'] - $donneesAnnuelles['masseSalariale']) }}"
                           class="form-control @error('benefice_imposable') is-invalid @enderror"
                           required oninput="calculerIS()">
                    @error('benefice_imposable')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">CA − charges − masse salariale (estimatif)</small>
                </div>
            </div>

            {{-- Section acompte --}}
            <div id="sect-acompte">
                <div class="mb-3">
                    <label class="form-label">IS de l'année précédente (base acompte) (FCFA)</label>
                    <input type="number" name="base_acompte" id="inp-base-acompte" step="1" min="0"
                           value="{{ old('base_acompte', $isAnneeN1) }}"
                           class="form-control" oninput="calculerIS()">
                    <small class="text-muted">Chaque acompte = IS N-1 ÷ 4</small>
                </div>
            </div>

            {{-- Section annuelle --}}
            <div id="sect-annuelle" style="display:none">
                <div class="mb-3">
                    <label class="form-label">Acomptes déjà versés cette année (FCFA)</label>
                    <input type="number" name="acomptes_verses" id="inp-acomptes" step="1" min="0"
                           value="{{ old('acomptes_verses', $acomptesVerses) }}"
                           class="form-control" oninput="calculerIS()">
                </div>
            </div>

            {{-- Résultat IS --}}
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-md-3">
                            <div class="text-muted small">IS brut (30 %)</div>
                            <div class="fw-bold" id="res-is-brut">—</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Min. forfaitaire (1 % CA)</div>
                            <div class="fw-bold" id="res-min">—</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">IS dû</div>
                            <div class="fw-bold text-warning" id="res-is-du">—</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small" id="lbl-final">Montant acompte</div>
                            <div class="fw-bold fs-5 text-danger" id="res-final">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Enregistrer</button>
                <a href="{{ route('impots.is.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
function fmt(n) { return Math.round(n).toLocaleString('fr-FR') + ' FCFA'; }

function toggleType() {
    const isAnnuelle = document.getElementById('sel-type').value === 'annuelle';
    document.getElementById('bloc-trimestre').style.display = isAnnuelle ? 'none' : '';
    document.getElementById('sect-acompte').style.display   = isAnnuelle ? 'none' : '';
    document.getElementById('sect-annuelle').style.display  = isAnnuelle ? '' : 'none';
    document.getElementById('lbl-final').textContent = isAnnuelle ? 'Complément dû' : 'Montant acompte';
    calculerIS();
}

function calculerIS() {
    const ca       = parseFloat(document.getElementById('inp-ca').value) || 0;
    const benefice = parseFloat(document.getElementById('inp-benefice').value) || 0;
    const isBrut   = benefice * 0.30;
    const minIS    = Math.max(ca * 0.01, 500000);
    const isDu     = Math.max(isBrut, minIS);

    document.getElementById('res-is-brut').textContent = fmt(isBrut);
    document.getElementById('res-min').textContent      = fmt(minIS);
    document.getElementById('res-is-du').textContent    = fmt(isDu);

    const isAnnuelle = document.getElementById('sel-type').value === 'annuelle';
    if (isAnnuelle) {
        const acomptes = parseFloat(document.getElementById('inp-acomptes').value) || 0;
        document.getElementById('res-final').textContent = fmt(Math.max(0, isDu - acomptes));
    } else {
        const baseAcompte = parseFloat(document.getElementById('inp-base-acompte').value) || 0;
        document.getElementById('res-final').textContent = fmt(baseAcompte / 4);
    }
}

document.addEventListener('DOMContentLoaded', () => { toggleType(); calculerIS(); });
</script>
@endpush
