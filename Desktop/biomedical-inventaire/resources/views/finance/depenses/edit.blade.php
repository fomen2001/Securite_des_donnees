@extends('layouts.app')

@section('title', 'Modifier une dépense')
@section('page-title', 'Modifier une dépense')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.depenses.index') }}">Dépenses</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-pencil me-2"></i>Modifier — {{ $depense->reference }}</div>
    <div class="card-body">
        <form action="{{ route('finance.depenses.update', $depense) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" value="{{ old('libelle', $depense->libelle) }}"
                           class="form-control @error('libelle') is-invalid @enderror" required>
                    @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                    <select name="categorie_depense_id" class="form-select @error('categorie_depense_id') is-invalid @enderror" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('categorie_depense_id', $depense->categorie_depense_id) == $cat->id)>
                                {{ $cat->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('categorie_depense_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Montant HT (FCFA) <span class="text-danger">*</span></label>
                    <input type="number" name="montant_ht" id="montant_ht"
                           value="{{ old('montant_ht', $depense->montant_ht) }}" min="0" step="1"
                           class="form-control @error('montant_ht') is-invalid @enderror" required
                           oninput="calculerTTC()">
                    @error('montant_ht')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">TVA (%)</label>
                    <input type="number" name="tva" id="tva"
                           value="{{ old('tva', $depense->tva) }}" min="0" max="100" step="0.01"
                           class="form-control" oninput="calculerTTC()">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Montant TTC (FCFA)</label>
                    <div class="input-group">
                        <input type="text" id="montant_ttc_display" class="form-control bg-light" readonly
                               value="{{ number_format($depense->montant_ttc, 0, ',', ' ') }}">
                        <span class="input-group-text">FCFA</span>
                    </div>
                    <input type="hidden" name="montant_ttc" id="montant_ttc" value="{{ old('montant_ttc', $depense->montant_ttc) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_depense"
                           value="{{ old('date_depense', $depense->date_depense->format('Y-m-d')) }}"
                           class="form-control @error('date_depense') is-invalid @enderror" required>
                    @error('date_depense')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mode de paiement</label>
                    <select name="mode_paiement" class="form-select">
                        @foreach(['especes'=>'Espèces','virement'=>'Virement','cheque'=>'Chèque','mobile_money'=>'Mobile Money','carte'=>'Carte','autre'=>'Autre'] as $v=>$l)
                            <option value="{{ $v }}" @selected(old('mode_paiement', $depense->mode_paiement) == $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">N° pièce justificative</label>
                    <input type="text" name="numero_piece" value="{{ old('numero_piece', $depense->numero_piece) }}" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Bénéficiaire</label>
                    <input type="text" name="beneficiaire" value="{{ old('beneficiaire', $depense->beneficiaire) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fournisseur</label>
                    <select name="fournisseur_id" class="form-select">
                        <option value="">— Aucun —</option>
                        @foreach($fournisseurs as $f)
                            <option value="{{ $f->id }}" @selected(old('fournisseur_id', $depense->fournisseur_id) == $f->id)>{{ $f->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $depense->notes) }}</textarea>
                </div>

                @if($depense->document_path)
                <div class="col-12">
                    <label class="form-label">Pièce jointe actuelle</label>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-paperclip text-muted"></i>
                        <a href="{{ Storage::url($depense->document_path) }}" target="_blank" class="small">
                            {{ basename($depense->document_path) }}
                        </a>
                    </div>
                </div>
                @endif
                <div class="col-12">
                    <label class="form-label">Remplacer la pièce jointe</label>
                    <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">PDF ou image, max 5 Mo</small>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                <a href="{{ route('finance.depenses.show', $depense) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
function calculerTTC() {
    const ht = parseFloat(document.getElementById('montant_ht').value) || 0;
    const tva = parseFloat(document.getElementById('tva').value) || 0;
    const ttc = ht * (1 + tva / 100);
    document.getElementById('montant_ttc').value = Math.round(ttc);
    document.getElementById('montant_ttc_display').value = Math.round(ttc).toLocaleString('fr-FR');
}
</script>
@endpush
