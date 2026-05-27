@extends('layouts.app')

@section('title', 'Mouvement de stock')
@section('page-title', 'Enregistrer un mouvement de stock')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<form method="POST" action="{{ route('mouvements.store') }}">
    @csrf

    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-arrow-left-right me-2 text-info"></i>Nouveau mouvement
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Équipement <span class="text-danger">*</span></label>
                    <select name="equipement_id" class="form-select @error('equipement_id') is-invalid @enderror" required
                            id="selectEquipement">
                        <option value="">Sélectionner...</option>
                        @foreach($equipements as $eq)
                            <option value="{{ $eq->id }}"
                                    data-qte="{{ $eq->quantite }}"
                                    data-service="{{ $eq->service_id }}"
                                    {{ (old('equipement_id', $equipement?->id)) == $eq->id ? 'selected' : '' }}>
                                [{{ $eq->code_inventaire }}] {{ $eq->designation }} — Stock: {{ $eq->quantite }}
                            </option>
                        @endforeach
                    </select>
                    @error('equipement_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Type de mouvement <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required id="selectType">
                        <option value="entree" {{ old('type') === 'entree' ? 'selected' : '' }}>Entrée stock (réception)</option>
                        <option value="sortie" {{ old('type') === 'sortie' ? 'selected' : '' }}>Sortie stock (mise en service)</option>
                        <option value="transfert" {{ old('type') === 'transfert' ? 'selected' : '' }}>Transfert entre services</option>
                        <option value="retour" {{ old('type') === 'retour' ? 'selected' : '' }}>Retour en stock</option>
                        <option value="ajustement" {{ old('type') === 'ajustement' ? 'selected' : '' }}>Ajustement inventaire</option>
                        <option value="reforme" {{ old('type') === 'reforme' ? 'selected' : '' }}>Réforme / Mise au rebut</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Quantité <span class="text-danger">*</span></label>
                    <input type="number" name="quantite" class="form-control @error('quantite') is-invalid @enderror"
                        value="{{ old('quantite', 1) }}" min="1" required>
                    <div class="form-text" id="stockActuel"></div>
                    @error('quantite')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6" id="divServiceSource">
                    <label class="form-label">Service source</label>
                    <select name="service_source_id" class="form-select">
                        <option value="">Aucun</option>
                        @foreach($services as $id => $nom)
                            <option value="{{ $id }}" {{ old('service_source_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6" id="divServiceDest">
                    <label class="form-label">Service destination</label>
                    <select name="service_destination_id" class="form-select">
                        <option value="">Aucun</option>
                        @foreach($services as $id => $nom)
                            <option value="{{ $id }}" {{ old('service_destination_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Référence document</label>
                    <input type="text" name="reference_document" class="form-control"
                        value="{{ old('reference_document') }}" placeholder="N° BL, facture...">
                </div>

                <div class="col-12">
                    <label class="form-label">Motif / Observations</label>
                    <textarea name="motif" class="form-control" rows="3"
                        placeholder="Raison du mouvement...">{{ old('motif') }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            @if($equipement)
                <a href="{{ route('equipements.show', $equipement) }}" class="btn btn-outline-secondary">Annuler</a>
            @else
                <a href="{{ route('mouvements.index') }}" class="btn btn-outline-secondary">Annuler</a>
            @endif
        </div>
    </div>
</form>
</div>
</div>
@endsection

@push('scripts')
<script>
    const sel = document.getElementById('selectEquipement');
    const info = document.getElementById('stockActuel');

    sel.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const qte = opt.dataset.qte;
        info.textContent = qte !== undefined ? `Stock actuel : ${qte} unité(s)` : '';
    });

    // Déclencher au chargement si présélection
    sel.dispatchEvent(new Event('change'));
</script>
@endpush
