@extends('layouts.app')

@section('title', 'Nouvelle vente')
@section('page-title', 'Nouvelle vente')

@push('styles')
<style>
    #tableau-lignes tbody tr td { vertical-align: middle; }
    .prix-ttc { font-weight: 600; color: #198754; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('ventes.store') }}" id="formVente">
    @csrf

    <div class="row g-4">

        {{-- En-tête vente --}}
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-cart3 me-2 text-success"></i>Informations de la vente
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">N° Facture</label>
                            <input type="text" class="form-control" value="{{ $numero }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date de vente <span class="text-danger">*</span></label>
                            <input type="date" name="date_vente" class="form-control"
                                value="{{ old('date_vente', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Livraison prévue</label>
                            <input type="date" name="date_livraison_prevue" class="form-control"
                                value="{{ old('date_livraison_prevue') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                <option value="">Sélectionner un client...</option>
                                @foreach($clients as $cl)
                                    <option value="{{ $cl->id }}"
                                            {{ (old('client_id', request('client_id'))) == $cl->id ? 'selected' : '' }}>
                                        [{{ $cl->code_client }}] {{ $cl->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mode de paiement</label>
                            <select name="mode_paiement" class="form-select" id="modePaiement">
                                <option value="especes">Espèces</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="virement">Virement bancaire</option>
                                <option value="cheque">Chèque</option>
                                <option value="credit">Crédit / Différé</option>
                            </select>
                        </div>
                        <div id="divEcheance" class="col-md-4" style="display:none">
                            <label class="form-label">Date d'échéance</label>
                            <input type="date" name="date_echeance" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lignes de la vente --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-list-ul me-2"></i>Articles</span>
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnAjouterLigne">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter un article
                    </button>
                </div>

                @error('lignes')
                    <div class="alert alert-danger mx-3 mt-2 py-2 small">{{ $message }}</div>
                @enderror

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" id="tableau-lignes">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:260px">Équipement</th>
                                <th style="width:80px">Qté dispo</th>
                                <th style="width:90px">Qté</th>
                                <th style="width:150px">Prix unit. HT (FCFA)</th>
                                <th style="width:80px">Remise %</th>
                                <th style="width:130px" class="text-end">Total HT</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="lignesBody">
                            {{-- Lignes ajoutées dynamiquement --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Récapitulatif --}}
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top:80px">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-calculator me-2 text-info"></i>Récapitulatif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small">Remise globale (%)</label>
                            <input type="number" name="remise_globale" id="remiseGlobale"
                                class="form-control form-control-sm" value="{{ old('remise_globale', 0) }}"
                                min="0" max="100" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">TVA (%)</label>
                            <input type="number" name="tva" id="tvaInput"
                                class="form-control form-control-sm" value="{{ old('tva', 19.25) }}"
                                min="0" max="100" step="0.01">
                        </div>
                    </div>

                    <hr>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Sous-total HT</td>
                            <td class="text-end fw-semibold" id="affSousTotal">0 FCFA</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Remise</td>
                            <td class="text-end text-danger" id="affRemise">- 0 FCFA</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Base HT nette</td>
                            <td class="text-end" id="affBaseHt">0 FCFA</td>
                        </tr>
                        <tr>
                            <td class="text-muted">TVA (<span id="affPctTva">19.25</span>%)</td>
                            <td class="text-end" id="affTva">0 FCFA</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold fs-5">Total TTC</td>
                            <td class="text-end fw-bold fs-5 text-success" id="affTotalTtc">0 FCFA</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-grow-1">
                        <i class="bi bi-check-circle me-1"></i>Confirmer la vente
                    </button>
                    <a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </div>
        </div>

    </div>
</form>

{{-- Données équipements pour JS --}}
<script>
const equipements = @json($equipementsJs);

let ligneIndex = 0;

function formatFCFA(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';
}

function recalculer() {
    let sousTotal = 0;
    document.querySelectorAll('.ligne-vente').forEach(row => {
        const qte  = parseFloat(row.querySelector('.qte').value)  || 0;
        const pu   = parseFloat(row.querySelector('.pu').value)   || 0;
        const rem  = parseFloat(row.querySelector('.rem').value)  || 0;
        const total = qte * pu * (1 - rem / 100);
        row.querySelector('.total-ht').textContent = formatFCFA(total);
        row.querySelector('.total-ht-hidden').value = total.toFixed(2);
        sousTotal += total;
    });

    const remisePct = parseFloat(document.getElementById('remiseGlobale').value) || 0;
    const tvaPct    = parseFloat(document.getElementById('tvaInput').value) || 0;
    const remise    = sousTotal * remisePct / 100;
    const baseHt    = sousTotal - remise;
    const tva       = baseHt * tvaPct / 100;
    const ttc       = baseHt + tva;

    document.getElementById('affSousTotal').textContent = formatFCFA(sousTotal);
    document.getElementById('affRemise').textContent    = '- ' + formatFCFA(remise);
    document.getElementById('affBaseHt').textContent    = formatFCFA(baseHt);
    document.getElementById('affPctTva').textContent    = tvaPct;
    document.getElementById('affTva').textContent       = formatFCFA(tva);
    document.getElementById('affTotalTtc').textContent  = formatFCFA(ttc);
}

function ajouterLigne(eq = null) {
    const i   = ligneIndex++;
    const row = document.createElement('tr');
    row.className = 'ligne-vente';
    row.innerHTML = `
        <td>
            <select name="lignes[${i}][equipement_id]" class="form-select form-select-sm eq-select" required>
                <option value="">Choisir...</option>
                ${equipements.map(e =>
                    `<option value="${e.id}" data-qte="${e.quantite}" data-prix="${e.prix}"
                             ${eq && eq.id === e.id ? 'selected' : ''}>
                        [${e.code}] ${e.designation} (stock: ${e.quantite})
                    </option>`
                ).join('')}
            </select>
        </td>
        <td class="text-center small text-muted dispo-qte">—</td>
        <td>
            <input type="number" name="lignes[${i}][quantite]" class="form-control form-control-sm qte"
                   value="1" min="1" required>
        </td>
        <td>
            <input type="number" name="lignes[${i}][prix_unitaire_ht]" class="form-control form-control-sm pu"
                   value="${eq ? eq.prix : 0}" min="0" step="1" required>
        </td>
        <td>
            <input type="number" name="lignes[${i}][remise]" class="form-control form-control-sm rem"
                   value="0" min="0" max="100" step="0.01">
        </td>
        <td class="text-end total-ht fw-semibold text-success">0 FCFA
            <input type="hidden" name="lignes[${i}][total_ht]" class="total-ht-hidden" value="0">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-suppr">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    // Pré-sélection
    if (eq) {
        const sel = row.querySelector('.eq-select');
        sel.value = eq.id;
        row.querySelector('.dispo-qte').textContent = eq.quantite;
        row.querySelector('.pu').value = eq.prix;
    }

    row.querySelector('.eq-select').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        row.querySelector('.dispo-qte').textContent = opt.dataset.qte || '—';
        row.querySelector('.pu').value = opt.dataset.prix || 0;
        recalculer();
    });

    row.querySelectorAll('.qte, .pu, .rem').forEach(el => el.addEventListener('input', recalculer));
    row.querySelector('.btn-suppr').addEventListener('click', () => { row.remove(); recalculer(); });

    document.getElementById('lignesBody').appendChild(row);
    recalculer();
}

document.getElementById('btnAjouterLigne').addEventListener('click', () => ajouterLigne());
document.getElementById('remiseGlobale').addEventListener('input', recalculer);
document.getElementById('tvaInput').addEventListener('input', recalculer);

document.getElementById('modePaiement').addEventListener('change', function () {
    document.getElementById('divEcheance').style.display = this.value === 'credit' ? 'block' : 'none';
});

// Ajouter une ligne vide au démarrage
ajouterLigne();
</script>
@endsection
