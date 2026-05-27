@extends('layouts.app')

@section('title', 'Générer un bulletin de paie')
@section('page-title', 'Générer un bulletin de paie')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.bulletins.index') }}">Bulletins</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<div class="row g-4">

    <div class="col-lg-7">
        <form id="form-bulletin" action="{{ route('rh.bulletins.store') }}" method="POST">
            @csrf

            <div class="card mb-4">
                <div class="card-header fw-semibold"><i class="bi bi-person me-2"></i>Employé & Période</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Employé <span class="text-danger">*</span></label>
                            <select name="employe_id" id="employe_id" class="form-select @error('employe_id') is-invalid @enderror" required>
                                <option value="">— Sélectionner un employé —</option>
                                @foreach($employes as $e)
                                    <option value="{{ $e->id }}"
                                        data-salaire="{{ $e->salaire_base }}"
                                        data-situation="{{ $e->situation_matrimoniale }}"
                                        data-enfants="{{ $e->nombre_enfants }}"
                                        @selected(old('employe_id', $employe?->id) == $e->id)>
                                        {{ $e->matricule }} – {{ $e->nom_complet }} ({{ $e->poste }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employe_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-4">
                            <label class="form-label">Mois <span class="text-danger">*</span></label>
                            <select name="mois" id="mois" class="form-select" required>
                                @foreach(['1'=>'Janvier','2'=>'Février','3'=>'Mars','4'=>'Avril','5'=>'Mai','6'=>'Juin','7'=>'Juillet','8'=>'Août','9'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'] as $m=>$l)
                                    <option value="{{ $m }}" @selected(old('mois', $mois) == $m)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Année <span class="text-danger">*</span></label>
                            <select name="annee" id="annee" class="form-select" required>
                                @foreach(range(now()->year, now()->year - 3) as $a)
                                    <option value="{{ $a }}" @selected(old('annee', $annee) == $a)>{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Jours travaillés</label>
                            <input type="number" name="jours_travailles" id="jours_travailles" value="{{ old('jours_travailles', 26) }}" min="1" max="31" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Heures supplémentaires</label>
                            <input type="number" name="heures_supplementaires" value="{{ old('heures_supplementaires', 0) }}" min="0" step="0.5" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Mode de paiement</label>
                            <select name="mode_paiement" class="form-select">
                                <option value="">— À définir —</option>
                                <option value="virement" @selected(old('mode_paiement')=='virement')>Virement bancaire</option>
                                <option value="especes" @selected(old('mode_paiement')=='especes')>Espèces</option>
                                <option value="cheque" @selected(old('mode_paiement')=='cheque')>Chèque</option>
                                <option value="mobile_money" @selected(old('mode_paiement')=='mobile_money')>Mobile Money</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header fw-semibold"><i class="bi bi-cash-stack me-2"></i>Rémunération</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Salaire de base (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="salaire_base" id="salaire_base" value="{{ old('salaire_base', $employe?->salaire_base) }}" class="form-control" required min="0" step="500" oninput="calculer()">
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>

                        {{-- Primes --}}
                        <div class="col-12">
                            <label class="form-label">Primes & Indemnités imposables</label>
                            <div id="primes-container">
                                <div class="prime-row row g-2 mb-2">
                                    <div class="col-7">
                                        <input type="text" name="details_primes[0][type]" class="form-control form-control-sm" placeholder="Ex. Prime de transport, de performance...">
                                    </div>
                                    <div class="col-4">
                                        <input type="number" name="details_primes[0][montant]" class="form-control form-control-sm prime-montant" placeholder="Montant" min="0" step="500" oninput="updateTotaux()">
                                    </div>
                                    <div class="col-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(this)"><i class="bi bi-x"></i></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="ajouterPrime()">
                                <i class="bi bi-plus me-1"></i>Ajouter une prime
                            </button>
                        </div>

                        {{-- Indemnités non imposables --}}
                        <div class="col-12">
                            <label class="form-label">Indemnités non imposables</label>
                            <div id="indemnites-container">
                                <div class="indem-row row g-2 mb-2">
                                    <div class="col-7">
                                        <input type="text" name="details_indemnites[0][type]" class="form-control form-control-sm" placeholder="Ex. Indemnité de logement...">
                                    </div>
                                    <div class="col-4">
                                        <input type="number" name="details_indemnites[0][montant]" class="form-control form-control-sm indem-montant" placeholder="Montant" min="0" step="500" oninput="updateTotaux()">
                                    </div>
                                    <div class="col-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(this)"><i class="bi bi-x"></i></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="ajouterIndemnite()">
                                <i class="bi bi-plus me-1"></i>Ajouter une indemnité
                            </button>
                        </div>

                        {{-- Totaux cachés --}}
                        <input type="hidden" name="total_primes" id="total_primes" value="0">
                        <input type="hidden" name="total_indemnites" id="total_indemnites" value="0">

                        <div class="col-6">
                            <label class="form-label">Avantages en nature (FCFA)</label>
                            <input type="number" name="avantages_nature" id="avantages_nature" value="{{ old('avantages_nature', 0) }}" min="0" step="500" class="form-control" oninput="calculer()">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Avances à déduire (FCFA)</label>
                            <input type="number" name="avances_deduites" id="avances_deduites" value="{{ old('avances_deduites', 0) }}" min="0" step="500" class="form-control" oninput="calculer()">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observations</label>
                            <textarea name="observations" class="form-control" rows="2">{{ old('observations') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg me-1"></i>Générer le bulletin
                </button>
                <a href="{{ route('rh.bulletins.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>

    {{-- Simulateur temps réel --}}
    <div class="col-lg-5">
        <div class="card sticky-top" style="top:80px">
            <div class="card-header fw-semibold bg-primary text-white">
                <i class="bi bi-calculator me-2"></i>Simulateur de paie – Loi camerounaise
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr class="table-light"><td colspan="2" class="fw-semibold small text-uppercase ps-3 py-1">GAINS</td></tr>
                        <tr><td class="ps-3">Salaire de base</td><td class="text-end pe-3 fw-semibold" id="s-base">0 FCFA</td></tr>
                        <tr><td class="ps-3">Primes (imposables)</td><td class="text-end pe-3" id="s-primes">0 FCFA</td></tr>
                        <tr><td class="ps-3">Indemnités</td><td class="text-end pe-3" id="s-indemnites">0 FCFA</td></tr>
                        <tr><td class="ps-3">Avantages en nature</td><td class="text-end pe-3" id="s-avantages">0 FCFA</td></tr>
                        <tr class="table-success fw-bold"><td class="ps-3">SALAIRE BRUT</td><td class="text-end pe-3" id="s-brut">0 FCFA</td></tr>

                        <tr class="table-light"><td colspan="2" class="fw-semibold small text-uppercase ps-3 py-1">RETENUES LÉGALES</td></tr>
                        <tr>
                            <td class="ps-3">
                                CNPS salarié (4,2 %)
                                <small class="text-muted d-block">Plafond 750 000 FCFA/mois</small>
                            </td>
                            <td class="text-end pe-3 text-danger" id="s-cnps">0 FCFA</td>
                        </tr>
                        <tr>
                            <td class="ps-3">
                                IRPP
                                <small class="text-muted d-block" id="s-irpp-detail">Barème progressif</small>
                            </td>
                            <td class="text-end pe-3 text-danger" id="s-irpp">0 FCFA</td>
                        </tr>
                        <tr>
                            <td class="ps-3">CAC (10 % IRPP)</td>
                            <td class="text-end pe-3 text-danger" id="s-cac">0 FCFA</td>
                        </tr>
                        <tr>
                            <td class="ps-3">RAV (Redevance audiovisuelle)</td>
                            <td class="text-end pe-3 text-danger" id="s-rav">2 500 FCFA</td>
                        </tr>
                        <tr>
                            <td class="ps-3">Avances déduites</td>
                            <td class="text-end pe-3 text-danger" id="s-avances">0 FCFA</td>
                        </tr>
                        <tr class="table-danger fw-bold"><td class="ps-3">TOTAL RETENUES</td><td class="text-end pe-3" id="s-retenues">0 FCFA</td></tr>

                        <tr class="table-light"><td colspan="2" class="fw-semibold small text-uppercase ps-3 py-1">CHARGE EMPLOYEUR</td></tr>
                        <tr>
                            <td class="ps-3">
                                CNPS employeur (~12,95 %)
                                <small class="text-muted d-block">Vieillesse + AF + AT</small>
                            </td>
                            <td class="text-end pe-3" id="s-cnps-emp">0 FCFA</td>
                        </tr>

                        <tr class="bg-success text-white fw-bold fs-6">
                            <td class="ps-3">NET À PAYER</td>
                            <td class="text-end pe-3" id="s-net">0 FCFA</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Calcul conforme : CNPS 4,2 % | IRPP barème progressif | CAC 10 % | RAV 2 500 FCFA/mois
                </small>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const SMIG = 41875;
const CNPS_PLAFOND = 750000;
const CNPS_TAUX = 0.042;
const CNPS_EMPLOYEUR = 0.1295;
const RAV = 2500;
const CAC_TAUX = 0.10;
const ABATTEMENT_TAUX = 0.30;
const ABATTEMENT_MIN = 500000;
const ABATTEMENT_MAX = 2500000;

const BAREME = [
    [0, 2000000, 0.10],
    [2000000, 3000000, 0.15],
    [3000000, 5000000, 0.25],
    [5000000, 10000000, 0.35],
    [10000000, Infinity, 0.385],
];

let primeCount = 1;
let indemCount = 1;

function fmt(n) {
    return Math.round(n).toLocaleString('fr-FR') + ' FCFA';
}

function bareme(revenu) {
    if (revenu <= 0) return 0;
    let impot = 0;
    for (const [min, max, taux] of BAREME) {
        if (revenu <= min) break;
        impot += (Math.min(revenu, max) - min) * taux;
    }
    return impot;
}

function calculerIRPP(brut, cnps, situation, enfants) {
    const rn = brut - cnps;
    const abatt = Math.max(ABATTEMENT_MIN / 12, Math.min(ABATTEMENT_MAX / 12, rn * ABATTEMENT_TAUX));
    const rni = Math.max(0, (rn - abatt) * 12);
    if (rni <= 0) return 0;

    let parts = situation === 'marie' ? 2.0 : (situation === 'veuf' ? 1.5 : 1.0);
    parts += Math.min(parseInt(enfants || 0) * 0.5, 4.5);
    parts = Math.min(parts, 6.5);

    const parPart = rni / parts;
    const impotUne = bareme(parPart);
    const credit = SMIG * 0.10 * 12;
    const impotAnnuel = Math.max(0, impotUne * parts - credit);
    return Math.round(impotAnnuel / 12);
}

function getSituationEnfants() {
    const sel = document.getElementById('employe_id');
    const opt = sel.options[sel.selectedIndex];
    return {
        situation: opt.dataset.situation || 'celibataire',
        enfants: opt.dataset.enfants || 0,
    };
}

function updateTotaux() {
    let totalPrimes = 0;
    document.querySelectorAll('.prime-montant').forEach(el => {
        totalPrimes += parseFloat(el.value) || 0;
    });
    let totalIndemnites = 0;
    document.querySelectorAll('.indem-montant').forEach(el => {
        totalIndemnites += parseFloat(el.value) || 0;
    });
    document.getElementById('total_primes').value = totalPrimes;
    document.getElementById('total_indemnites').value = totalIndemnites;
    calculer();
}

function calculer() {
    const base       = parseFloat(document.getElementById('salaire_base').value) || 0;
    const primes     = parseFloat(document.getElementById('total_primes').value) || 0;
    const indemnites = parseFloat(document.getElementById('total_indemnites').value) || 0;
    const avantages  = parseFloat(document.getElementById('avantages_nature').value) || 0;
    const avances    = parseFloat(document.getElementById('avances_deduites').value) || 0;
    const { situation, enfants } = getSituationEnfants();

    const brut = base + primes + indemnites + avantages;
    const baseCnps = Math.min(brut, CNPS_PLAFOND);
    const cnps = Math.round(baseCnps * CNPS_TAUX);
    const cnpsEmp = Math.round(baseCnps * CNPS_EMPLOYEUR);
    const irpp = calculerIRPP(brut, cnps, situation, enfants);
    const cac = Math.round(irpp * CAC_TAUX);
    const totalRetenues = cnps + irpp + cac + RAV + avances;
    const net = Math.max(0, brut - totalRetenues);

    document.getElementById('s-base').textContent = fmt(base);
    document.getElementById('s-primes').textContent = fmt(primes);
    document.getElementById('s-indemnites').textContent = fmt(indemnites);
    document.getElementById('s-avantages').textContent = fmt(avantages);
    document.getElementById('s-brut').textContent = fmt(brut);
    document.getElementById('s-cnps').textContent = '– ' + fmt(cnps);
    document.getElementById('s-irpp').textContent = '– ' + fmt(irpp);
    document.getElementById('s-cac').textContent = '– ' + fmt(cac);
    document.getElementById('s-rav').textContent = '– ' + fmt(RAV);
    document.getElementById('s-avances').textContent = avances > 0 ? '– ' + fmt(avances) : '0 FCFA';
    document.getElementById('s-retenues').textContent = fmt(totalRetenues);
    document.getElementById('s-cnps-emp').textContent = fmt(cnpsEmp);
    document.getElementById('s-net').textContent = fmt(net);

    // Détail IRPP
    const parPart = brut > 0 ? 'Revenu brut ' + fmt(brut) : '';
    document.getElementById('s-irpp-detail').textContent = parPart;
}

function ajouterPrime() {
    const cont = document.getElementById('primes-container');
    const idx = primeCount++;
    cont.insertAdjacentHTML('beforeend', `
        <div class="prime-row row g-2 mb-2">
            <div class="col-7"><input type="text" name="details_primes[${idx}][type]" class="form-control form-control-sm" placeholder="Type de prime"></div>
            <div class="col-4"><input type="number" name="details_primes[${idx}][montant]" class="form-control form-control-sm prime-montant" placeholder="Montant" min="0" step="500" oninput="updateTotaux()"></div>
            <div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(this)"><i class="bi bi-x"></i></button></div>
        </div>`);
}

function ajouterIndemnite() {
    const cont = document.getElementById('indemnites-container');
    const idx = indemCount++;
    cont.insertAdjacentHTML('beforeend', `
        <div class="indem-row row g-2 mb-2">
            <div class="col-7"><input type="text" name="details_indemnites[${idx}][type]" class="form-control form-control-sm" placeholder="Type d'indemnité"></div>
            <div class="col-4"><input type="number" name="details_indemnites[${idx}][montant]" class="form-control form-control-sm indem-montant" placeholder="Montant" min="0" step="500" oninput="updateTotaux()"></div>
            <div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerLigne(this)"><i class="bi bi-x"></i></button></div>
        </div>`);
}

function supprimerLigne(btn) {
    btn.closest('.row').remove();
    updateTotaux();
}

// Pré-remplir le salaire quand on sélectionne un employé
document.getElementById('employe_id').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const salaire = opt.dataset.salaire;
    if (salaire) document.getElementById('salaire_base').value = salaire;
    calculer();
});

// Calcul initial
calculer();
</script>
@endpush
