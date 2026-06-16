@extends('layouts.app')

@section('title', 'Nouveau bon de réception')
@section('page-title', 'Saisie d\'une réception')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achats.receptions.index') }}">Réceptions</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
@endsection

@section('content')
<form action="{{ route('achats.receptions.store') }}" method="POST" id="form-br">
@csrf

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-file-earmark-text me-2"></i>Bon de commande</div>
            <div class="card-body">
                @if($bonCommande)
                    <div class="alert alert-info small py-2">
                        <strong>{{ $bonCommande->numero }}</strong><br>
                        {{ $bonCommande->fournisseur->nom }}<br>
                        {{ $bonCommande->date_commande->format('d/m/Y') }}
                    </div>
                    <input type="hidden" name="bon_commande_id" value="{{ $bonCommande->id }}">
                @else
                    <label class="form-label">Sélectionner un BC <span class="text-danger">*</span></label>
                    <select name="bon_commande_id" class="form-select @error('bon_commande_id') is-invalid @enderror" required
                            onchange="this.form.action='{{ route('achats.receptions.create') }}?bon_commande_id='+this.value; this.form.method='GET'; this.form.submit()">
                        <option value="">— Sélectionner —</option>
                        @foreach($commandesOuvertes as $bc)
                            <option value="{{ $bc->id }}">{{ $bc->numero }} — {{ $bc->fournisseur->nom }}</option>
                        @endforeach
                    </select>
                    @error('bon_commande_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @endif
            </div>
        </div>

        @if($bonCommande)
        <div class="card mb-3">
            <div class="card-header fw-semibold">Informations réception</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Date de réception <span class="text-danger">*</span></label>
                    <input type="date" name="date_reception" value="{{ old('date_reception', now()->format('Y-m-d')) }}"
                           class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Transporteur</label>
                    <input type="text" name="transporteur" value="{{ old('transporteur') }}" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">N° BL Fournisseur</label>
                    <input type="text" name="numero_bl_fournisseur" value="{{ old('numero_bl_fournisseur') }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Observations</label>
                    <textarea name="observations" class="form-control" rows="3">{{ old('observations') }}</textarea>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($bonCommande)
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-boxes me-2"></i>Lignes à réceptionner
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Article</th>
                                <th class="text-center">Commandé</th>
                                <th class="text-center">Déjà reçu</th>
                                <th class="text-center">Restant</th>
                                <th class="text-center">Reçu aujourd'hui</th>
                                <th class="text-center">Conforme</th>
                                <th>Motif rejet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bonCommande->lignes as $i => $ligne)
                            @php $restant = max(0, $ligne->quantite_commandee - $ligne->quantite_recue); @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold small">{{ $ligne->designation }}</div>
                                    @if($ligne->reference_fournisseur)
                                    <div class="text-muted x-small">Réf: {{ $ligne->reference_fournisseur }}</div>
                                    @endif
                                    <input type="hidden" name="lignes[{{ $i }}][bon_commande_ligne_id]" value="{{ $ligne->id }}">
                                </td>
                                <td class="text-center">{{ $ligne->quantite_commandee }} {{ $ligne->unite }}</td>
                                <td class="text-center text-muted">{{ $ligne->quantite_recue }}</td>
                                <td class="text-center {{ $restant > 0 ? 'text-warning fw-semibold' : 'text-success' }}">
                                    {{ $restant }}
                                </td>
                                <td class="text-center">
                                    <input type="number" name="lignes[{{ $i }}][quantite_recue]"
                                           id="rec-{{ $i }}" class="form-control form-control-sm text-center"
                                           min="0" step="0.01" value="0" max="{{ $restant }}"
                                           oninput="majConforme({{ $i }})">
                                </td>
                                <td class="text-center">
                                    <input type="number" name="lignes[{{ $i }}][quantite_conforme]"
                                           id="conf-{{ $i }}" class="form-control form-control-sm text-center"
                                           min="0" step="0.01" value="0">
                                </td>
                                <td>
                                    <input type="text" name="lignes[{{ $i }}][motif_rejet]"
                                           class="form-control form-control-sm" placeholder="Si rejet…">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex gap-2 justify-content-end">
                <a href="{{ route('achats.receptions.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Enregistrer la réception
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
</form>
@endsection

@push('scripts')
<script>
function majConforme(idx) {
    const rec  = parseFloat(document.getElementById('rec-'  + idx)?.value) || 0;
    const conf = document.getElementById('conf-' + idx);
    if (conf && parseFloat(conf.value) > rec) conf.value = rec;
    if (conf && !parseFloat(conf.value)) conf.value = rec;
}
</script>
@endpush
