@extends('layouts.app')

@section('title', 'Bilan comptable')
@section('page-title', 'Saisie du bilan comptable — SYSCOHADA')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item"><a href="{{ route('impots.bilan.index') }}">Bilan</a></li>
    <li class="breadcrumb-item active">Saisie</li>
@endsection

@section('content')
<div class="alert alert-info small mb-4">
    <i class="bi bi-magic me-1"></i>
    Le CA, les charges de personnel et certaines charges ont été <strong>pré-remplis</strong> depuis les modules Ventes, Dépenses et RH pour l'exercice {{ $exercice }}.
    Complétez les autres rubriques selon votre comptabilité.
</div>

<form action="{{ route('impots.bilan.store') }}" method="POST">
@csrf
<input type="hidden" name="exercice" value="{{ $exercice }}">

<div class="row g-4">

{{-- ── ACTIF ──────────────────────────────────────────────── --}}
<div class="col-lg-6">

<div class="card mb-4">
    <div class="card-header fw-semibold bg-primary text-white">ACTIF</div>

    <div class="card-body">
        <h6 class="text-muted mb-3">A — Actif immobilisé</h6>
        @foreach([
            'immob_incorporelles' => 'Immo. incorporelles (brevets, logiciels, fonds commercial)',
            'immob_corporelles'   => 'Immo. corporelles (matériel, bâtiments, terrains)',
            'immob_financieres'   => 'Immo. financières (titres, prêts LT)',
        ] as $f => $l)
        <div class="mb-2">
            <label class="form-label small">{{ $l }}</label>
            <div class="input-group input-group-sm">
                <input type="number" name="{{ $f }}" step="1" min="0"
                       value="{{ old($f, $existant?->$f ?? 0) }}" class="form-control" oninput="recalculer()">
                <span class="input-group-text">FCFA</span>
            </div>
        </div>
        @endforeach
        <div class="text-end small fw-semibold text-primary mt-1">Total A = <span id="total-ai">0</span> FCFA</div>
        <hr>

        <h6 class="text-muted mb-3">B — Actif circulant</h6>
        @foreach([
            'stocks'          => 'Stocks et encours',
            'creances_clients'=> 'Créances clients et rattachées',
            'tva_recuperable' => 'Crédit TVA récupérable',
            'autres_creances' => 'Autres créances',
        ] as $f => $l)
        <div class="mb-2">
            <label class="form-label small">{{ $l }}</label>
            <div class="input-group input-group-sm">
                <input type="number" name="{{ $f }}" step="1" min="0"
                       value="{{ old($f, $existant?->$f ?? 0) }}" class="form-control" oninput="recalculer()">
                <span class="input-group-text">FCFA</span>
            </div>
        </div>
        @endforeach
        <div class="text-end small fw-semibold text-primary mt-1">Total B = <span id="total-ac">0</span> FCFA</div>
        <hr>

        <h6 class="text-muted mb-3">C — Trésorerie-Actif</h6>
        <div class="mb-2">
            <label class="form-label small">Banques, CCP, Caisse</label>
            <div class="input-group input-group-sm">
                <input type="number" name="banques_caisse" step="1" min="0"
                       value="{{ old('banques_caisse', $existant?->banques_caisse ?? 0) }}" class="form-control" oninput="recalculer()">
                <span class="input-group-text">FCFA</span>
            </div>
        </div>
        <div class="text-end small fw-semibold text-primary mt-1">Total C = <span id="total-ta">0</span> FCFA</div>
    </div>
    <div class="card-footer fw-bold text-end text-primary">TOTAL ACTIF = <span id="total-actif">0</span> FCFA</div>
</div>

{{-- Compte de résultat --}}
<div class="card mb-4">
    <div class="card-header fw-semibold bg-secondary text-white">Compte de Résultat</div>
    <div class="card-body">
        <h6 class="text-muted mb-2">Produits</h6>
        @php $ca = old('chiffre_affaires', $existant?->chiffre_affaires ?? $ca) @endphp
        <div class="mb-2">
            <label class="form-label small">Chiffre d'affaires HT</label>
            <div class="input-group input-group-sm">
                <input type="number" name="chiffre_affaires" step="1" min="0"
                       value="{{ $ca }}" class="form-control" oninput="recalculerCR()">
                <span class="input-group-text">FCFA</span>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label small">Autres produits d'exploitation</label>
            <div class="input-group input-group-sm">
                <input type="number" name="autres_produits" step="1" min="0"
                       value="{{ old('autres_produits', $existant?->autres_produits ?? 0) }}" class="form-control" oninput="recalculerCR()">
                <span class="input-group-text">FCFA</span>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label small">Produits financiers</label>
            <div class="input-group input-group-sm">
                <input type="number" name="produits_financiers" step="1" min="0"
                       value="{{ old('produits_financiers', $existant?->produits_financiers ?? 0) }}" class="form-control" oninput="recalculerCR()">
                <span class="input-group-text">FCFA</span>
            </div>
        </div>
        <hr>
        <h6 class="text-muted mb-2">Charges</h6>
        @foreach([
            'achats_consommes'            => ['Achats consommés (matières, marchandises)', old('achats_consommes', $existant?->achats_consommes ?? $charges)],
            'charges_personnel'           => ['Charges de personnel (brut + CNPS employeur)', old('charges_personnel', $existant?->charges_personnel ?? $chargesPersonnel)],
            'dotations_amortissements'    => ['Dotations aux amortissements', old('dotations_amortissements', $existant?->dotations_amortissements ?? 0)],
            'autres_charges_exploitation' => ['Autres charges d\'exploitation', old('autres_charges_exploitation', $existant?->autres_charges_exploitation ?? 0)],
            'charges_financieres'         => ['Charges financières (intérêts)', old('charges_financieres', $existant?->charges_financieres ?? 0)],
            'is_exerce'                   => ['IS de l\'exercice', old('is_exerce', $existant?->is_exerce ?? 0)],
        ] as $f => [$l, $v])
        <div class="mb-2">
            <label class="form-label small">{{ $l }}</label>
            <div class="input-group input-group-sm">
                <input type="number" name="{{ $f }}" step="1" min="0"
                       value="{{ $v }}" class="form-control" oninput="recalculerCR()">
                <span class="input-group-text">FCFA</span>
            </div>
        </div>
        @endforeach
        <div class="card bg-light mt-2 p-2 text-center">
            <div class="small text-muted">Résultat net de l'exercice</div>
            <div class="fw-bold fs-5" id="res-resultat-net">—</div>
        </div>
    </div>
</div>

</div>{{-- col actif --}}

{{-- ── PASSIF ─────────────────────────────────────────────── --}}
<div class="col-lg-6">

<div class="card mb-4">
    <div class="card-header fw-semibold bg-success text-white">PASSIF</div>
    <div class="card-body">
        <h6 class="text-muted mb-3">I — Capitaux propres</h6>
        @foreach([
            'capital_social'   => 'Capital social (min. 1 000 000 FCFA pour SARL)',
            'reserves'         => 'Réserves (légale 5 %, min. 500 000)',
            'report_a_nouveau' => 'Report à nouveau (+ ou −)',
        ] as $f => $l)
        <div class="mb-2">
            <label class="form-label small">{{ $l }}</label>
            <div class="input-group input-group-sm">
                <input type="number" name="{{ $f }}" step="1"
                       value="{{ old($f, $existant?->$f ?? 0) }}" class="form-control" oninput="recalculer()">
                <span class="input-group-text">FCFA</span>
            </div>
        </div>
        @endforeach
        <div class="mb-2">
            <label class="form-label small text-muted">Résultat net (calculé automatiquement)</label>
            <input type="hidden" name="resultat_exercice" id="hid-resultat" value="0">
            <input type="text" id="disp-resultat" class="form-control form-control-sm bg-light" readonly value="0 FCFA">
        </div>
        <div class="text-end small fw-semibold text-success mt-1">Total I = <span id="total-cp">0</span> FCFA</div>
        <hr>

        <h6 class="text-muted mb-3">II — Dettes financières (long terme)</h6>
        @foreach([
            'emprunts_long_terme'       => 'Emprunts bancaires LT',
            'autres_dettes_financieres' => 'Autres dettes financières',
        ] as $f => $l)
        <div class="mb-2">
            <label class="form-label small">{{ $l }}</label>
            <div class="input-group input-group-sm">
                <input type="number" name="{{ $f }}" step="1" min="0"
                       value="{{ old($f, $existant?->$f ?? 0) }}" class="form-control" oninput="recalculer()">
                <span class="input-group-text">FCFA</span>
            </div>
        </div>
        @endforeach
        <div class="text-end small fw-semibold text-success mt-1">Total II = <span id="total-df">0</span> FCFA</div>
        <hr>

        <h6 class="text-muted mb-3">E — Passif circulant</h6>
        @foreach([
            'dettes_fournisseurs'       => 'Dettes fournisseurs',
            'dettes_fiscales'           => 'Dettes fiscales (TVA à payer, IS à payer)',
            'dettes_sociales'           => 'Dettes sociales (CNPS, salaires à payer)',
            'autres_dettes_court_terme' => 'Autres dettes à court terme',
        ] as $f => $l)
        <div class="mb-2">
            <label class="form-label small">{{ $l }}</label>
            <div class="input-group input-group-sm">
                <input type="number" name="{{ $f }}" step="1" min="0"
                       value="{{ old($f, $existant?->$f ?? 0) }}" class="form-control" oninput="recalculer()">
                <span class="input-group-text">FCFA</span>
            </div>
        </div>
        @endforeach
        <div class="text-end small fw-semibold text-success mt-1">Total E = <span id="total-pc">0</span> FCFA</div>
    </div>
    <div class="card-footer fw-bold text-end text-success">TOTAL PASSIF = <span id="total-passif">0</span> FCFA</div>
</div>

{{-- Équilibre + notes --}}
<div id="alerte-equilibre" class="alert d-none mb-3"></div>

<div class="card mb-4">
    <div class="card-body">
        <label class="form-label">Notes / Annexes</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Méthodes comptables, événements post-clôture, engagements hors bilan...">{{ old('notes', $existant?->notes) }}</textarea>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save me-1"></i>Enregistrer le bilan</button>
    <a href="{{ route('impots.bilan.index') }}" class="btn btn-outline-secondary btn-lg">Annuler</a>
</div>

</div>{{-- col passif --}}
</div>{{-- row --}}
</form>
@endsection

@push('scripts')
<script>
function v(id) { return parseFloat(document.querySelector('[name="'+id+'"]')?.value) || 0; }
function fmt(n) { return Math.round(n).toLocaleString('fr-FR') + ' FCFA'; }
function set(id, n) { const el = document.getElementById(id); if(el) el.textContent = fmt(n); }

function recalculerCR() {
    const ca    = v('chiffre_affaires');
    const autP  = v('autres_produits');
    const pFin  = v('produits_financiers');
    const ach   = v('achats_consommes');
    const pers  = v('charges_personnel');
    const amort = v('dotations_amortissements');
    const autC  = v('autres_charges_exploitation');
    const cFin  = v('charges_financieres');
    const is    = v('is_exerce');

    const produits = ca + autP;
    const chargesExp = ach + pers + amort + autC;
    const rex   = produits - chargesExp;
    const rav   = rex + pFin - cFin;
    const rNet  = rav - is;

    const el = document.getElementById('res-resultat-net');
    if(el) {
        el.textContent = fmt(rNet);
        el.className = 'fw-bold fs-5 ' + (rNet >= 0 ? 'text-success' : 'text-danger');
    }

    // Mettre à jour le champ caché et le champ affiché
    const hidR = document.getElementById('hid-resultat');
    if(hidR) hidR.value = Math.round(rNet);
    const dispR = document.getElementById('disp-resultat');
    if(dispR) dispR.value = fmt(rNet);

    recalculer();
}

function recalculer() {
    const ii   = v('immob_incorporelles');
    const ic   = v('immob_corporelles');
    const ifin = v('immob_financieres');
    const totalAI = ii + ic + ifin;

    const st   = v('stocks');
    const cc   = v('creances_clients');
    const tvr  = v('tva_recuperable');
    const autC = v('autres_creances');
    const totalAC = st + cc + tvr + autC;

    const tresor = v('banques_caisse');
    const totalActif = totalAI + totalAC + tresor;

    set('total-ai', totalAI);
    set('total-ac', totalAC);
    set('total-ta', tresor);
    set('total-actif', totalActif);

    const cap  = v('capital_social');
    const res  = v('reserves');
    const ran  = v('report_a_nouveau');
    const rnet = parseFloat(document.getElementById('hid-resultat')?.value) || 0;
    const totalCP = cap + res + ran + rnet;

    const elt  = v('emprunts_long_terme');
    const adf  = v('autres_dettes_financieres');
    const totalDF = elt + adf;

    const dfou = v('dettes_fournisseurs');
    const dfis = v('dettes_fiscales');
    const dsoc = v('dettes_sociales');
    const autD = v('autres_dettes_court_terme');
    const totalPC = dfou + dfis + dsoc + autD;

    const totalPassif = totalCP + totalDF + totalPC;

    set('total-cp', totalCP);
    set('total-df', totalDF);
    set('total-pc', totalPC);
    set('total-passif', totalPassif);

    // Vérification équilibre
    const diff = Math.abs(totalActif - totalPassif);
    const alerte = document.getElementById('alerte-equilibre');
    if(alerte) {
        if(diff < 1) {
            alerte.className = 'alert alert-success mb-3';
            alerte.innerHTML = '<i class="bi bi-check-circle me-2"></i>Bilan équilibré : Actif = Passif = ' + fmt(totalActif);
        } else {
            alerte.className = 'alert alert-danger mb-3';
            alerte.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Déséquilibre : Actif (' + fmt(totalActif) + ') ≠ Passif (' + fmt(totalPassif) + ') — écart : ' + fmt(diff);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => { recalculerCR(); });
</script>
@endpush
