@extends('layouts.app')

@section('title', 'Nouvelle patente')
@section('page-title', 'Patente — Déclaration et calcul')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item"><a href="{{ route('impots.patente.index') }}">Patente</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">

@if($existante)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Une patente existe déjà pour {{ $annee }}. Ce formulaire la mettra à jour.
</div>
@endif

<div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-building me-2"></i>Patente {{ $annee }}</div>
    <div class="card-body">
        <form action="{{ route('impots.patente.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Année <span class="text-danger">*</span></label>
                    <input type="number" name="annee" value="{{ old('annee', $annee) }}" min="2020" class="form-control" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Échéance légale</label>
                    <input type="text" class="form-control bg-light" value="{{ $echeance->format('d/m/Y') }}" readonly>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">CA de référence (N-1) en FCFA <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="chiffre_affaires_reference" id="inp-ca"
                           value="{{ old('chiffre_affaires_reference', $caReference) }}"
                           min="0" step="1" class="form-control" required oninput="calculer()">
                    <span class="input-group-text">FCFA</span>
                </div>
                <small class="text-muted">Pré-rempli depuis les ventes de {{ $annee - 1 }}</small>
            </div>

            {{-- Résultat calculé --}}
            <div class="card bg-light mb-4">
                <div class="card-header small fw-semibold">Calcul automatique (CGI Cameroun)</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">Droit fixe</label>
                            <div class="input-group">
                                <input type="number" name="droit_fixe" id="res-df" step="1" min="0"
                                       value="{{ old('droit_fixe', $calcul['droitFixe']) }}"
                                       class="form-control @error('droit_fixe') is-invalid @enderror">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Droit variable (0,159 % CA)</label>
                            <div class="input-group">
                                <input type="number" name="droit_variable" id="res-dv" step="1" min="0"
                                       value="{{ old('droit_variable', $calcul['droitVariable']) }}"
                                       class="form-control @error('droit_variable') is-invalid @enderror">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">CAC (10 % × [fixe + variable])</label>
                            <div class="input-group">
                                <input type="number" name="centimes_additionnels" id="res-cac" step="1" min="0"
                                       value="{{ old('centimes_additionnels', $calcul['centimesAdditionnels']) }}"
                                       class="form-control @error('centimes_additionnels') is-invalid @enderror">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="w-100 text-center border rounded p-2 bg-white">
                                <div class="text-muted small">TOTAL</div>
                                <div class="fw-bold fs-4 text-danger" id="res-total">
                                    {{ number_format($calcul['montantTotal'], 0, ',', ' ') }} FCFA
                                </div>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">Vous pouvez ajuster les montants manuellement si nécessaire.</small>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $existante?->notes) }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Enregistrer</button>
                <a href="{{ route('impots.patente.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
function droitFixe(ca) {
    if (ca <= 5e6)   return 0;
    if (ca <= 10e6)  return 60000;
    if (ca <= 30e6)  return 80000;
    if (ca <= 100e6) return 150000;
    if (ca <= 500e6) return 250000;
    if (ca <= 1e9)   return 500000;
    return 1000000;
}

function calculer() {
    const ca  = parseFloat(document.getElementById('inp-ca').value) || 0;
    const df  = droitFixe(ca);
    const dv  = Math.round(ca * 0.00159);
    const cac = Math.round((df + dv) * 0.10);
    const tot = df + dv + cac;

    document.getElementById('res-df').value  = df;
    document.getElementById('res-dv').value  = dv;
    document.getElementById('res-cac').value = cac;
    document.getElementById('res-total').textContent = Math.round(tot).toLocaleString('fr-FR') + ' FCFA';
}

// Recalcul du total quand les champs sont modifiés manuellement
['res-df', 'res-dv', 'res-cac'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', () => {
        const df  = parseFloat(document.getElementById('res-df').value) || 0;
        const dv  = parseFloat(document.getElementById('res-dv').value) || 0;
        const cac = parseFloat(document.getElementById('res-cac').value) || 0;
        document.getElementById('res-total').textContent = Math.round(df+dv+cac).toLocaleString('fr-FR') + ' FCFA';
    });
});
</script>
@endpush
