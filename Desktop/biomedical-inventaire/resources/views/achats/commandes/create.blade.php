@extends('layouts.app')

@section('title', 'Nouveau bon de commande')
@section('page-title', 'Nouveau bon de commande fournisseur')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achats.commandes.index') }}">Bons de commande</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<form action="{{ route('achats.commandes.store') }}" method="POST" id="form-bc">
@csrf

<div class="row g-4">

    {{-- Colonne gauche --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-truck me-2"></i>Fournisseur</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Fournisseur <span class="text-danger">*</span></label>
                    <select name="fournisseur_id" id="sel-fournisseur" class="form-select @error('fournisseur_id') is-invalid @enderror" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($fournisseurs as $f)
                            <option value="{{ $f->id }}"
                                data-contact="{{ $f->contact_nom }}"
                                data-tel="{{ $f->telephone }}"
                                data-email="{{ $f->email }}"
                                {{ old('fournisseur_id') == $f->id ? 'selected' : '' }}>
                                {{ $f->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('fournisseur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div id="info-fournisseur" class="small text-muted d-none">
                    <div id="f-contact"></div>
                    <div id="f-tel"></div>
                    <div id="f-email"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-calendar3 me-2"></i>Dates & Conditions</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Date de commande <span class="text-danger">*</span></label>
                    <input type="date" name="date_commande" value="{{ old('date_commande', now()->format('Y-m-d')) }}"
                           class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Livraison souhaitée</label>
                    <input type="date" name="date_livraison_souhaitee" value="{{ old('date_livraison_souhaitee') }}"
                           class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">TVA (%)</label>
                    <input type="number" name="taux_tva" id="taux-tva" value="{{ old('taux_tva', 19.25) }}"
                           min="0" max="100" step="0.01" class="form-control" oninput="recalculerTotal()">
                </div>
                <div class="mb-3">
                    <label class="form-label">Conditions de paiement</label>
                    <input type="text" name="conditions" value="{{ old('conditions') }}"
                           class="form-control" placeholder="Ex: 30 jours fin de mois">
                </div>
                <div>
                    <label class="form-label">Notes internes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Récapitulatif --}}
        <div class="card border-primary">
            <div class="card-header fw-semibold text-primary">Récapitulatif</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Total HT</td><td class="text-end fw-semibold" id="recap-ht">0 FCFA</td></tr>
                    <tr><td class="text-muted">TVA</td><td class="text-end" id="recap-tva">0 FCFA</td></tr>
                    <tr class="fw-bold"><td>TOTAL TTC</td><td class="text-end text-primary fs-5" id="recap-ttc">0 FCFA</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Colonne droite : lignes --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                <span><i class="bi bi-list-ul me-2"></i>Articles commandés</span>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="ajouterLigne()">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="tbl-lignes">
                        <thead class="table-light">
                            <tr>
                                <th style="width:30%">Désignation</th>
                                <th style="width:14%">Référence</th>
                                <th style="width:10%">Qté</th>
                                <th style="width:9%">Unité</th>
                                <th style="width:16%">P.U. HT (FCFA)</th>
                                <th style="width:15%">Total HT</th>
                                <th style="width:6%"></th>
                            </tr>
                        </thead>
                        <tbody id="lignes-body">
                            {{-- Lignes injectées par JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex gap-2 justify-content-end">
                <a href="{{ route('achats.commandes.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" name="action" value="brouillon" class="btn btn-secondary">
                    <i class="bi bi-floppy me-1"></i>Enregistrer brouillon
                </button>
                <button type="submit" name="action" value="confirmer" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i>Confirmer la commande
                </button>
            </div>
        </div>
    </div>

</div>
</form>
@endsection

@push('scripts')
@php
    $equipementsJs = $equipements->map(fn($e) => ['id' => $e->id, 'nom' => $e->nom, 'ref' => $e->reference, 'prix' => $e->prix_unitaire]);
@endphp
<script>
const equipements = @json($equipementsJs);

let ligneIdx = 0;

function rowHtml(idx) {
    const opts = equipements.map(e =>
        `<option value="${e.id}" data-prix="${e.prix}" data-ref="${e.ref}">${e.nom}</option>`
    ).join('');
    return `<tr id="row-${idx}">
        <td>
            <select class="form-select form-select-sm mb-1" onchange="remplirLigne(${idx},this)" name="lignes[${idx}][equipement_id]">
                <option value="">— Équipement (optionnel) —</option>${opts}
            </select>
            <input type="text" name="lignes[${idx}][designation]" class="form-control form-control-sm" placeholder="Désignation *" required>
        </td>
        <td><input type="text" name="lignes[${idx}][reference_fournisseur]" id="ref-${idx}" class="form-control form-control-sm" placeholder="Réf."></td>
        <td><input type="number" name="lignes[${idx}][quantite_commandee]" id="qte-${idx}" class="form-control form-control-sm" min="0.01" step="0.01" value="1" oninput="calculerLigne(${idx})" required></td>
        <td><input type="text" name="lignes[${idx}][unite]" class="form-control form-control-sm" value="unité"></td>
        <td><input type="number" name="lignes[${idx}][prix_unitaire_ht]" id="pu-${idx}" class="form-control form-control-sm" min="0" step="1" value="0" oninput="calculerLigne(${idx})" required></td>
        <td><input type="number" id="ht-${idx}" class="form-control form-control-sm bg-light" readonly value="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(${idx})"><i class="bi bi-trash"></i></button></td>
    </tr>`;
}

function ajouterLigne() {
    document.getElementById('lignes-body').insertAdjacentHTML('beforeend', rowHtml(ligneIdx++));
}

function supprimerLigne(idx) {
    document.getElementById('row-' + idx)?.remove();
    recalculerTotal();
}

function remplirLigne(idx, sel) {
    const opt = sel.selectedOptions[0];
    if (!opt.value) return;
    const prix = opt.dataset.prix || 0;
    const ref  = opt.dataset.ref  || '';
    document.getElementById('pu-'  + idx).value = prix;
    document.getElementById('ref-' + idx).value = ref;
    calculerLigne(idx);
}

function calculerLigne(idx) {
    const q  = parseFloat(document.getElementById('qte-' + idx)?.value) || 0;
    const pu = parseFloat(document.getElementById('pu-'  + idx)?.value) || 0;
    const ht = Math.round(q * pu);
    const el = document.getElementById('ht-' + idx);
    if (el) el.value = ht;
    recalculerTotal();
}

function recalculerTotal() {
    const tva = parseFloat(document.getElementById('taux-tva').value) || 0;
    let totalHt = 0;
    document.querySelectorAll('[id^="ht-"]').forEach(el => {
        totalHt += parseFloat(el.value) || 0;
    });
    const montantTva = Math.round(totalHt * tva / 100);
    const ttc        = totalHt + montantTva;
    document.getElementById('recap-ht').textContent  = Math.round(totalHt).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('recap-tva').textContent = montantTva.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('recap-ttc').textContent = ttc.toLocaleString('fr-FR') + ' FCFA';
}

// Infos fournisseur
document.getElementById('sel-fournisseur').addEventListener('change', function () {
    const opt = this.selectedOptions[0];
    const div = document.getElementById('info-fournisseur');
    if (!opt.value) { div.classList.add('d-none'); return; }
    document.getElementById('f-contact').textContent = opt.dataset.contact || '';
    document.getElementById('f-tel').textContent     = opt.dataset.tel    || '';
    document.getElementById('f-email').textContent   = opt.dataset.email  || '';
    div.classList.remove('d-none');
});

// Ajouter 1 ligne par défaut
ajouterLigne();
</script>
@endpush
