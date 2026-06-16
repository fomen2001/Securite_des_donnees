@extends('layouts.app')

@section('title', 'Nouveau bon de livraison')
@section('page-title', 'Nouveau bon de livraison')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achats.livraisons.index') }}">Livraisons</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<form action="{{ route('achats.livraisons.store') }}" method="POST" id="form-bl">
@csrf

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-people me-2"></i>Client & Vente</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Vente liée (optionnel)</label>
                    <select name="vente_id" id="sel-vente" class="form-select" onchange="remplirDepuisVente(this)">
                        <option value="">— Sélectionner une vente —</option>
                        @foreach($ventes as $v)
                            <option value="{{ $v->id }}"
                                data-client="{{ $v->client_id }}"
                                data-client-nom="{{ $v->client->nom }}"
                                {{ old('vente_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->numero_facture }} — {{ $v->client->nom }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Permet de marquer la vente comme livrée automatiquement.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Client <span class="text-danger">*</span></label>
                    <select name="client_id" id="sel-client" class="form-select @error('client_id') is-invalid @enderror" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}"
                                data-adresse="{{ $c->adresse }}"
                                data-tel="{{ $c->telephone }}"
                                {{ old('client_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-geo-alt me-2"></i>Livraison</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Date de livraison <span class="text-danger">*</span></label>
                    <input type="date" name="date_livraison" value="{{ old('date_livraison', now()->format('Y-m-d')) }}"
                           class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Adresse de livraison</label>
                    <input type="text" name="adresse_livraison" id="inp-adresse"
                           value="{{ old('adresse_livraison') }}" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Transporteur</label>
                    <input type="text" name="transporteur" value="{{ old('transporteur') }}" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact réception</label>
                    <input type="text" name="contact_reception" value="{{ old('contact_reception') }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Observations</label>
                    <textarea name="observations" class="form-control" rows="2">{{ old('observations') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                <span><i class="bi bi-boxes me-2"></i>Articles à livrer</span>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="ajouterLigne()">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35%">Désignation</th>
                                <th style="width:15%">Référence</th>
                                <th style="width:12%">Quantité</th>
                                <th style="width:10%">Unité</th>
                                <th style="width:22%">Observations</th>
                                <th style="width:6%"></th>
                            </tr>
                        </thead>
                        <tbody id="lignes-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex gap-2 justify-content-end">
                <a href="{{ route('achats.livraisons.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Créer le bon de livraison
                </button>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
@php
    $equipementsJs = $equipements->map(fn($e) => ['id' => $e->id, 'nom' => $e->nom, 'ref' => $e->reference, 'stock' => $e->quantite]);
    $clientsJs     = $clients->map(fn($c) => ['id' => $c->id, 'adresse' => $c->adresse, 'tel' => $c->telephone]);
@endphp
<script>
const equipements = @json($equipementsJs);
const clients     = @json($clientsJs);
let ligneIdx = 0;

function rowHtml(idx, designation='', ref='', qte=1, unite='unité', equipId='') {
    const opts = equipements.map(e =>
        `<option value="${e.id}" data-ref="${e.ref}" ${e.id==equipId?'selected':''}>${e.nom} (stock:${e.stock})</option>`
    ).join('');
    return `<tr id="row-${idx}">
        <td>
            <select class="form-select form-select-sm mb-1" name="lignes[${idx}][equipement_id]" onchange="remplirLigneBL(${idx},this)">
                <option value="">— Équipement (optionnel) —</option>${opts}
            </select>
            <input type="text" name="lignes[${idx}][designation]" class="form-control form-control-sm"
                   placeholder="Désignation *" value="${designation}" required>
        </td>
        <td><input type="text" name="lignes[${idx}][reference]" id="blref-${idx}" class="form-control form-control-sm" value="${ref}"></td>
        <td><input type="number" name="lignes[${idx}][quantite]" class="form-control form-control-sm text-center" min="0.01" step="0.01" value="${qte}" required></td>
        <td><input type="text" name="lignes[${idx}][unite]" class="form-control form-control-sm" value="${unite}"></td>
        <td><input type="text" name="lignes[${idx}][observations]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(${idx})"><i class="bi bi-trash"></i></button></td>
    </tr>`;
}

function ajouterLigne(designation='', ref='', qte=1, unite='unité', equipId='') {
    document.getElementById('lignes-body').insertAdjacentHTML('beforeend', rowHtml(ligneIdx++, designation, ref, qte, unite, equipId));
}

function supprimerLigne(idx) {
    document.getElementById('row-' + idx)?.remove();
}

function remplirLigneBL(idx, sel) {
    const opt = sel.selectedOptions[0];
    if (!opt.value) return;
    const refEl = document.getElementById('blref-' + idx);
    if (refEl) refEl.value = opt.dataset.ref || '';
    const desEl = sel.closest('td').querySelector('input[type="text"]');
    if (desEl && !desEl.value) desEl.value = opt.text.split(' (stock')[0];
}

function remplirDepuisVente(sel) {
    const opt = sel.selectedOptions[0];
    if (!opt.value) return;
    // Sélectionner le client
    const clientSel = document.getElementById('sel-client');
    clientSel.value = opt.dataset.client;
    // Adresse
    const cli = clients.find(c => c.id == opt.dataset.client);
    if (cli) document.getElementById('inp-adresse').value = cli.adresse || '';
}

// Mise à jour adresse depuis client
document.getElementById('sel-client').addEventListener('change', function() {
    const cli = clients.find(c => c.id == this.value);
    if (cli) document.getElementById('inp-adresse').value = cli.adresse || '';
});

// Ligne par défaut
ajouterLigne();
</script>
@endpush
