@extends('layouts.app')

@section('title', 'Nouvelle dépense')
@section('page-title', 'Enregistrer une dépense')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.depenses.index') }}">Dépenses</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<form action="{{ route('finance.depenses.store') }}" method="POST">
    @csrf

    <div class="card mb-4">
        <div class="card-header fw-semibold"><i class="bi bi-wallet2 me-2"></i>Informations générales</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Référence <span class="text-danger">*</span></label>
                    <input type="text" name="reference" value="{{ old('reference', $reference) }}" class="form-control @error('reference') is-invalid @enderror" required>
                    @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Date de la dépense <span class="text-danger">*</span></label>
                    <input type="date" name="date_depense" value="{{ old('date_depense', now()->format('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Libellé / Description <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" value="{{ old('libelle') }}" class="form-control @error('libelle') is-invalid @enderror" required placeholder="Ex. Achat fournitures de bureau, Loyer local...">
                    @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                    <select name="categorie_depense_id" class="form-select @error('categorie_depense_id') is-invalid @enderror" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('categorie_depense_id') == $cat->id)>
                                {{ $cat->nom }} ({{ $cat->type_label }})
                            </option>
                        @endforeach
                    </select>
                    @error('categorie_depense_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Fournisseur / Prestataire</label>
                    <select name="fournisseur_id" class="form-select">
                        <option value="">— Aucun —</option>
                        @foreach($fournisseurs as $f)
                            <option value="{{ $f->id }}" @selected(old('fournisseur_id') == $f->id)>{{ $f->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Bénéficiaire</label>
                    <input type="text" name="beneficiaire" value="{{ old('beneficiaire') }}" class="form-control" placeholder="Nom du bénéficiaire">
                </div>
                <div class="col-6">
                    <label class="form-label">N° Pièce justificative</label>
                    <input type="text" name="numero_piece" value="{{ old('numero_piece') }}" class="form-control" placeholder="Facture, reçu...">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold"><i class="bi bi-cash me-2"></i>Montant & Paiement</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-4">
                    <label class="form-label">Montant HT (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" name="montant_ht" id="montant_ht" value="{{ old('montant_ht') }}" class="form-control @error('montant_ht') is-invalid @enderror" required min="0" step="500" oninput="calculerTTC()">
                    @error('montant_ht')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-4">
                    <label class="form-label">TVA (%)</label>
                    <select name="tva" id="tva_select" class="form-select" onchange="calculerTTC()">
                        <option value="0" @selected(old('tva','0')=='0')>0 % (exonéré)</option>
                        <option value="19.25" @selected(old('tva')=='19.25')>19,25 % (taux normal)</option>
                        <option value="11" @selected(old('tva')=='11')>11 % (taux réduit)</option>
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label">Montant TTC (FCFA)</label>
                    <input type="text" id="montant_ttc_display" class="form-control bg-light" readonly placeholder="Calculé auto.">
                </div>
                <div class="col-6">
                    <label class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                    <select name="mode_paiement" class="form-select" required>
                        <option value="especes" @selected(old('mode_paiement')=='especes')>Espèces</option>
                        <option value="virement" @selected(old('mode_paiement')=='virement')>Virement bancaire</option>
                        <option value="cheque" @selected(old('mode_paiement')=='cheque')>Chèque</option>
                        <option value="mobile_money" @selected(old('mode_paiement')=='mobile_money')>Mobile Money</option>
                        <option value="carte" @selected(old('mode_paiement')=='carte')>Carte bancaire</option>
                        <option value="autre" @selected(old('mode_paiement')=='autre')>Autre</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes / Observations</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-danger">
            <i class="bi bi-check-lg me-1"></i>Enregistrer la dépense
        </button>
        <a href="{{ route('finance.depenses.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
</div>
</div>
@endsection

@push('scripts')
<script>
function calculerTTC() {
    const ht  = parseFloat(document.getElementById('montant_ht').value) || 0;
    const tva = parseFloat(document.getElementById('tva_select').value) || 0;
    const ttc = ht * (1 + tva / 100);
    document.getElementById('montant_ttc_display').value = Math.round(ttc).toLocaleString('fr-FR') + ' FCFA';
}
calculerTTC();
</script>
@endpush
