@extends('layouts.app')

@section('title', 'Déclaration TVA')
@section('page-title', 'Nouvelle déclaration de TVA')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item"><a href="{{ route('impots.tva.index') }}">TVA</a></li>
    <li class="breadcrumb-item active">Nouvelle déclaration</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="alert alert-info small mb-4">
    <i class="bi bi-magic me-1"></i>
    Les montants ont été <strong>pré-remplis automatiquement</strong> depuis les ventes et dépenses enregistrées.
    Vous pouvez les ajuster si nécessaire.
</div>

<div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-receipt me-2"></i>Déclaration TVA — {{ $moisNom }} {{ $annee }}</div>
    <div class="card-body">
        <form action="{{ route('impots.tva.store') }}" method="POST">
            @csrf
            <input type="hidden" name="periode_mois" value="{{ $mois }}">
            <input type="hidden" name="periode_annee" value="{{ $annee }}">

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Mois</label>
                    <input type="text" class="form-control bg-light" value="{{ $moisNom }} {{ $annee }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date d'échéance légale</label>
                    <input type="text" class="form-control bg-light" value="{{ $echeance->format('d/m/Y') }}" readonly>
                </div>
            </div>

            {{-- TVA Collectée --}}
            <div class="card bg-success bg-opacity-10 border-success mb-3">
                <div class="card-header small fw-semibold text-success">TVA collectée (sur ventes)</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">CA HT facturé (FCFA)</label>
                            <input type="number" name="ventes_ht" id="ventes_ht" step="1" min="0"
                                   value="{{ old('ventes_ht', $donnees['ventes_ht']) }}"
                                   class="form-control @error('ventes_ht') is-invalid @enderror"
                                   oninput="recalculer()">
                            @error('ventes_ht')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TVA collectée (19,25 %)</label>
                            <div class="input-group">
                                <input type="number" name="tva_collectee" id="tva_collectee" step="1" min="0"
                                       value="{{ old('tva_collectee', $donnees['tva_collectee']) }}"
                                       class="form-control @error('tva_collectee') is-invalid @enderror"
                                       oninput="recalculer()">
                                <span class="input-group-text">FCFA</span>
                            </div>
                            @error('tva_collectee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- TVA Déductible --}}
            <div class="card bg-danger bg-opacity-10 border-danger mb-3">
                <div class="card-header small fw-semibold text-danger">TVA déductible (sur achats)</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Achats HT (FCFA)</label>
                            <input type="number" name="achats_ht" id="achats_ht" step="1" min="0"
                                   value="{{ old('achats_ht', $donnees['achats_ht']) }}"
                                   class="form-control" oninput="recalculer()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TVA déductible (FCFA)</label>
                            <div class="input-group">
                                <input type="number" name="tva_deductible" id="tva_deductible" step="1" min="0"
                                       value="{{ old('tva_deductible', $donnees['tva_deductible']) }}"
                                       class="form-control @error('tva_deductible') is-invalid @enderror"
                                       oninput="recalculer()">
                                <span class="input-group-text">FCFA</span>
                            </div>
                            @error('tva_deductible')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Crédit antérieur --}}
            <div class="mb-3">
                <label class="form-label">Crédit TVA reporté de la période précédente (FCFA)</label>
                <input type="number" name="credit_anterieur" id="credit_anterieur" step="1" min="0"
                       value="{{ old('credit_anterieur', $donnees['credit_anterieur']) }}"
                       class="form-control" oninput="recalculer()">
                <small class="text-muted">Renseigné automatiquement depuis la déclaration précédente.</small>
            </div>

            {{-- Résultat calculé --}}
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-muted small">TVA nette</div>
                            <div class="fw-bold fs-5" id="res-nette">—</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Montant à payer</div>
                            <div class="fw-bold fs-4 text-danger" id="res-payer">—</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Crédit nouveau</div>
                            <div class="fw-bold fs-5 text-info" id="res-credit">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Enregistrer</button>
                <a href="{{ route('impots.tva.index') }}" class="btn btn-outline-secondary">Annuler</a>
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

function recalculer() {
    const collectee   = parseFloat(document.getElementById('tva_collectee').value) || 0;
    const deductible  = parseFloat(document.getElementById('tva_deductible').value) || 0;
    const credit      = parseFloat(document.getElementById('credit_anterieur').value) || 0;
    const nette       = collectee - deductible - credit;

    document.getElementById('res-nette').textContent = fmt(nette);
    if (nette > 0) {
        document.getElementById('res-payer').textContent  = fmt(nette);
        document.getElementById('res-credit').textContent = '0 FCFA';
        document.getElementById('res-payer').className   = 'fw-bold fs-4 text-danger';
    } else {
        document.getElementById('res-payer').textContent  = '0 FCFA';
        document.getElementById('res-credit').textContent = fmt(Math.abs(nette));
        document.getElementById('res-payer').className   = 'fw-bold fs-4 text-muted';
    }
}

document.addEventListener('DOMContentLoaded', recalculer);
</script>
@endpush
