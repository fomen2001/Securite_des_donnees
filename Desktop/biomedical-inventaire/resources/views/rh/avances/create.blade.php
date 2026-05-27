@extends('layouts.app')

@section('title', 'Demande d\'avance sur salaire')
@section('page-title', 'Demande d\'avance sur salaire')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.avances.index') }}">Avances</a></li>
    <li class="breadcrumb-item active">Nouvelle demande</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card">
    <div class="card-header fw-semibold"><i class="bi bi-cash me-2"></i>Demande d'avance sur salaire</div>
    <div class="card-body">
        <form action="{{ route('rh.avances.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Employé <span class="text-danger">*</span></label>
                <select name="employe_id" id="employe_id" class="form-select @error('employe_id') is-invalid @enderror" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}"
                            data-salaire="{{ $e->salaire_base }}"
                            @selected(old('employe_id', $employe?->id) == $e->id)>
                            {{ $e->matricule }} – {{ $e->nom_complet }} ({{ number_format($e->salaire_base, 0, ',', ' ') }} FCFA)
                        </option>
                    @endforeach
                </select>
                @error('employe_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div id="info-limite" class="mt-1 small text-muted"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Montant de l'avance (FCFA) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="montant" id="montant"
                           value="{{ old('montant') }}" min="1000" step="500"
                           class="form-control @error('montant') is-invalid @enderror" required
                           oninput="verifierMontant()">
                    <span class="input-group-text">FCFA</span>
                </div>
                <div id="alerte-montant" class="mt-1 small"></div>
                @error('montant')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Date de l'avance <span class="text-danger">*</span></label>
                <input type="date" name="date_avance" value="{{ old('date_avance', now()->format('Y-m-d')) }}" class="form-control" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">Mois de déduction <span class="text-danger">*</span></label>
                    <select name="mois_deduction" class="form-select @error('mois_deduction') is-invalid @enderror" required>
                        @foreach(['1'=>'Janvier','2'=>'Février','3'=>'Mars','4'=>'Avril','5'=>'Mai','6'=>'Juin','7'=>'Juillet','8'=>'Août','9'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'] as $m=>$l)
                            <option value="{{ $m }}" @selected(old('mois_deduction', now()->month) == $m)>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('mois_deduction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6">
                    <label class="form-label">Année <span class="text-danger">*</span></label>
                    <select name="annee_deduction" class="form-select" required>
                        @foreach(range(now()->year, now()->year + 1) as $a)
                            <option value="{{ $a }}" @selected(old('annee_deduction', now()->year) == $a)>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Motif de la demande</label>
                <textarea name="motif" class="form-control" rows="2" placeholder="Raison de la demande d'avance...">{{ old('motif') }}</textarea>
            </div>

            <div class="alert alert-info small">
                <i class="bi bi-info-circle me-1"></i>
                L'avance est limitée à <strong>50 % du salaire de base</strong> et sera automatiquement déduite du bulletin de paie du mois sélectionné.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Soumettre</button>
                <a href="{{ route('rh.avances.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('employe_id').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const sal = parseFloat(opt.dataset.salaire) || 0;
    const limite = sal * 0.5;
    document.getElementById('info-limite').textContent = sal > 0
        ? `Limite : 50 % de ${sal.toLocaleString('fr-FR')} = ${limite.toLocaleString('fr-FR')} FCFA`
        : '';
    document.getElementById('montant').max = limite;
    verifierMontant();
});

function verifierMontant() {
    const sel = document.getElementById('employe_id');
    const opt = sel.options[sel.selectedIndex];
    const sal = parseFloat(opt.dataset.salaire) || 0;
    const montant = parseFloat(document.getElementById('montant').value) || 0;
    const limite = sal * 0.5;
    const el = document.getElementById('alerte-montant');
    if (sal > 0 && montant > limite) {
        el.textContent = `⚠️ Dépasse la limite de ${limite.toLocaleString('fr-FR')} FCFA (50% du salaire)`;
        el.className = 'mt-1 small text-danger';
    } else if (montant > 0) {
        el.textContent = `✓ Montant autorisé`;
        el.className = 'mt-1 small text-success';
    } else {
        el.textContent = '';
    }
}
</script>
@endpush
