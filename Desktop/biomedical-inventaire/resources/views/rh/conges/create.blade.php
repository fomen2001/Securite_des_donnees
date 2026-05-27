@extends('layouts.app')

@section('title', 'Demande de congé')
@section('page-title', 'Nouvelle demande de congé')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.conges.index') }}">Congés</a></li>
    <li class="breadcrumb-item active">Nouvelle demande</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-calendar-plus me-2"></i>Demande de congé</div>
            <div class="card-body">
                <form action="{{ route('rh.conges.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Employé <span class="text-danger">*</span></label>
                        <select name="employe_id" id="employe_id" class="form-select @error('employe_id') is-invalid @enderror" required>
                            <option value="">— Sélectionner —</option>
                            @foreach($employes as $e)
                                <option value="{{ $e->id }}"
                                    data-solde="{{ $e->solde_conge }}"
                                    @selected(old('employe_id', $employe?->id) == $e->id)>
                                    {{ $e->matricule }} – {{ $e->nom_complet }}
                                    (Solde : {{ $e->solde_conge }} j.)
                                </option>
                            @endforeach
                        </select>
                        @error('employe_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="info-solde" class="mt-1 small text-info"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type de congé <span class="text-danger">*</span></label>
                        <select name="type_conge" id="type_conge" class="form-select @error('type_conge') is-invalid @enderror" required>
                            @foreach([
                                'annuel'     => 'Congé annuel (payé — déduit du solde)',
                                'maladie'    => 'Congé maladie (certificat médical requis)',
                                'maternite'  => 'Congé maternité (14 semaines — Art. 84 Code Travail)',
                                'paternite'  => 'Congé paternité (10 jours ouvrables)',
                                'sans_solde' => 'Congé sans solde',
                                'deuil'      => 'Congé de deuil',
                                'autre'      => 'Autre',
                            ] as $v => $l)
                            <option value="{{ $v }}" @selected(old('type_conge') == $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                        @error('type_conge')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Date de début <span class="text-danger">*</span></label>
                            <input type="date" name="date_debut" id="date_debut" value="{{ old('date_debut') }}" class="form-control @error('date_debut') is-invalid @enderror" required oninput="calculerJours()">
                            @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date de fin <span class="text-danger">*</span></label>
                            <input type="date" name="date_fin" id="date_fin" value="{{ old('date_fin') }}" class="form-control @error('date_fin') is-invalid @enderror" required oninput="calculerJours()">
                            @error('date_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div id="info-jours" class="alert alert-info mb-3 d-none">
                        <i class="bi bi-calendar-check me-2"></i>
                        <strong id="jours-calcules">0</strong> jour(s) ouvrable(s) calculé(s)
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Motif / Précisions</label>
                        <textarea name="motif" class="form-control" rows="3" placeholder="Précisez le motif de la demande...">{{ old('motif') }}</textarea>
                        @error('motif')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i>Soumettre la demande
                        </button>
                        <a href="{{ route('rh.conges.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Rappel des droits --}}
        <div class="card mt-3">
            <div class="card-header fw-semibold small"><i class="bi bi-info-circle me-2"></i>Droits à congé — Code du Travail du Cameroun</div>
            <div class="card-body small">
                <ul class="mb-0">
                    <li><strong>Congé annuel</strong> : 1,5 jour ouvrable par mois travaillé = <strong>18 jours/an minimum</strong></li>
                    <li>Ancienneté ≥ 5 ans : +1 j | ≥ 10 ans : +2 j | ≥ 15 ans : +3 j | ≥ 20 ans : +4 j</li>
                    <li><strong>Maternité</strong> : 14 semaines (98 jours) — Art. 84 Code du Travail</li>
                    <li><strong>Paternité</strong> : 10 jours ouvrables</li>
                    <li><strong>Deuil</strong> : selon le degré de parenté (conjoint/enfant = 5 j, parent = 3 j)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('employe_id').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const solde = opt.dataset.solde || 0;
    document.getElementById('info-solde').textContent =
        solde > 0 ? `Solde disponible : ${solde} jour(s)` : '';
});

function calculerJours() {
    const debut = document.getElementById('date_debut').value;
    const fin = document.getElementById('date_fin').value;
    if (!debut || !fin) return;

    const d = new Date(debut);
    const f = new Date(fin);
    if (f < d) return;

    let jours = 0;
    const curr = new Date(d);
    while (curr <= f) {
        if (curr.getDay() !== 0) jours++; // exclure dimanches
        curr.setDate(curr.getDate() + 1);
    }
    document.getElementById('jours-calcules').textContent = jours;
    document.getElementById('info-jours').classList.remove('d-none');
}
</script>
@endpush
