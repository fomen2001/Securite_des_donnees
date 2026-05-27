@extends('layouts.app')

@section('title', 'Génération en masse')
@section('page-title', 'Génération en masse des bulletins de paie')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.bulletins.index') }}">Bulletins</a></li>
    <li class="breadcrumb-item active">Génération en masse</li>
@endsection

@section('content')
<form action="{{ route('rh.masse.store') }}" method="POST" id="form-masse">
@csrf

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label fw-semibold">Mois <span class="text-danger">*</span></label>
        <select name="mois" id="sel-mois" class="form-select" required>
            @foreach(['1'=>'Janvier','2'=>'Février','3'=>'Mars','4'=>'Avril','5'=>'Mai','6'=>'Juin',
                      '7'=>'Juillet','8'=>'Août','9'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'] as $m=>$l)
                <option value="{{ $m }}" @selected($mois == $m)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label fw-semibold">Année <span class="text-danger">*</span></label>
        <select name="annee" id="sel-annee" class="form-select" required>
            @foreach(range(now()->year - 1, now()->year + 1) as $a)
                <option value="{{ $a }}" @selected($annee == $a)>{{ $a }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Mode de paiement</label>
        <select name="mode_paiement" class="form-select">
            <option value="virement">Virement bancaire</option>
            <option value="especes">Espèces</option>
            <option value="cheque">Chèque</option>
            <option value="mobile_money">Mobile Money</option>
        </select>
    </div>
    <div class="col-md-4 d-flex align-items-end gap-2">
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="select-all">
            <label class="form-check-label" for="select-all">Tout sélectionner</label>
        </div>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card mb-4">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2"></i>Employés actifs — {{ $moisNom }} {{ $annee }}</span>
        <span class="badge bg-primary" id="badge-selectionnes">0 sélectionné(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:40px"></th>
                    <th>Employé</th>
                    <th>Poste</th>
                    <th class="text-end">Salaire brut</th>
                    <th class="text-end">Avance déduite</th>
                    <th class="text-end text-success">Net estimé</th>
                    <th class="text-center">Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employes as $e)
                @php $dejaGenere = isset($bulletinsExistants[$e->id]); @endphp
                <tr class="{{ $dejaGenere ? 'table-warning opacity-75' : '' }}">
                    <td>
                        @if(!$dejaGenere)
                        <input class="form-check-input employe-check" type="checkbox"
                               name="employes[]" value="{{ $e->id }}"
                               data-net="{{ $e->_net ?? 0 }}">
                        @else
                        <i class="bi bi-check-circle-fill text-success" title="Déjà généré"></i>
                        @endif
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $e->nom_complet }}</span>
                        <small class="text-muted d-block">{{ $e->matricule }}</small>
                    </td>
                    <td><small>{{ $e->poste }}</small></td>
                    <td class="text-end">{{ number_format($e->salaire_base, 0, ',', ' ') }} FCFA</td>
                    <td class="text-end text-danger">
                        @if(isset($avances[$e->id]) && $avances[$e->id] > 0)
                            −{{ number_format($avances[$e->id], 0, ',', ' ') }} FCFA
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-end fw-semibold text-success">
                        @if($dejaGenere)
                            <small class="text-muted">Voir bulletin</small>
                        @else
                            {{ number_format($e->_net ?? 0, 0, ',', ' ') }} FCFA
                        @endif
                    </td>
                    <td class="text-center">
                        @if($dejaGenere)
                            @php $b = $bulletinsExistants[$e->id]; @endphp
                            <a href="{{ route('rh.bulletins.show', $b->id) }}"
                               class="badge bg-{{ $b->statut === 'paye' ? 'success' : ($b->statut === 'valide' ? 'primary' : 'secondary') }} text-decoration-none">
                                {{ ucfirst($b->statut) }}
                            </a>
                        @else
                            <span class="badge bg-light text-dark">À générer</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-3 d-block mb-2"></i>Aucun employé actif.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employes->isNotEmpty())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            <strong id="count-a-generer">{{ $employes->where(fn($e) => !isset($bulletinsExistants[$e->id]))->count() }}</strong>
            employé(s) sans bulletin ce mois
        </div>
        <div class="fw-semibold">
            Masse salariale nette estimée : <span id="total-net" class="text-success">0 FCFA</span>
        </div>
    </div>
    @endif
</div>

<div class="alert alert-info small">
    <i class="bi bi-info-circle me-1"></i>
    Les bulletins générés auront le statut <strong>Validé</strong>. Les avances approuvées pour ce mois seront automatiquement déduites et marquées comme remboursées.
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary" id="btn-generer" disabled>
        <i class="bi bi-lightning-fill me-1"></i>Générer les bulletins sélectionnés
    </button>
    <a href="{{ route('rh.bulletins.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>

</form>
@endsection

@push('scripts')
<script>
const checks = document.querySelectorAll('.employe-check');
const selectAll = document.getElementById('select-all');
const badge = document.getElementById('badge-selectionnes');
const totalEl = document.getElementById('total-net');
const btnGenerer = document.getElementById('btn-generer');

function mettreAJour() {
    const selectionnes = [...checks].filter(c => c.checked);
    const count = selectionnes.length;
    badge.textContent = count + ' sélectionné(s)';
    const total = selectionnes.reduce((s, c) => s + parseFloat(c.dataset.net || 0), 0);
    totalEl.textContent = total.toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + ' FCFA';
    btnGenerer.disabled = count === 0;
}

checks.forEach(c => c.addEventListener('change', mettreAJour));

selectAll.addEventListener('change', function() {
    checks.forEach(c => { c.checked = this.checked; });
    mettreAJour();
});

mettreAJour();
</script>
@endpush
