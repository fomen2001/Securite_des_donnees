@extends('layouts.app')

@section('title', 'Déclaration TVA — ' . $tva->periode_label)
@section('page-title', 'Déclaration TVA — ' . $tva->periode_label)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item"><a href="{{ route('impots.tva.index') }}">TVA</a></li>
    <li class="breadcrumb-item active">{{ $tva->periode_label }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-receipt me-2"></i>TVA {{ $tva->periode_label }}</span>
        <span class="badge bg-{{ $tva->statut_badge }} fs-6">{{ $tva->statut_label }}</span>
    </div>
    <div class="card-body">

        {{-- Détail des montants --}}
        <table class="table table-bordered mb-4">
            <tbody>
                <tr class="table-success">
                    <td class="fw-semibold">CA HT facturé</td>
                    <td class="text-end">{{ number_format($tva->ventes_ht, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="table-success">
                    <td class="fw-semibold">TVA collectée (19,25 %)</td>
                    <td class="text-end fw-bold">{{ number_format($tva->tva_collectee, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="table-danger">
                    <td class="fw-semibold">Achats HT</td>
                    <td class="text-end">{{ number_format($tva->achats_ht, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="table-danger">
                    <td class="fw-semibold">TVA déductible</td>
                    <td class="text-end fw-bold">− {{ number_format($tva->tva_deductible, 0, ',', ' ') }} FCFA</td>
                </tr>
                @if($tva->credit_anterieur > 0)
                <tr class="table-info">
                    <td class="fw-semibold">Crédit TVA antérieur</td>
                    <td class="text-end fw-bold">− {{ number_format($tva->credit_anterieur, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
                <tr class="{{ $tva->tva_nette >= 0 ? 'table-warning' : 'table-primary' }}">
                    <td class="fw-bold">TVA nette</td>
                    <td class="text-end fw-bold">{{ number_format($tva->tva_nette, 0, ',', ' ') }} FCFA</td>
                </tr>
                @if($tva->montant_a_payer > 0)
                <tr class="table-danger">
                    <td class="fw-bold fs-5">MONTANT À PAYER</td>
                    <td class="text-end fw-bold fs-5 text-danger">{{ number_format($tva->montant_a_payer, 0, ',', ' ') }} FCFA</td>
                </tr>
                @else
                <tr class="table-info">
                    <td class="fw-bold">Crédit à reporter</td>
                    <td class="text-end fw-bold text-info">{{ number_format($tva->credit_nouveau, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- Informations légales --}}
        <div class="row g-3 mb-4">
            <div class="col-6"><small class="text-muted">Échéance légale</small><div class="fw-semibold">{{ $tva->date_echeance->format('d/m/Y') }}</div></div>
            @if($tva->date_paiement)
            <div class="col-6"><small class="text-muted">Date de paiement</small><div class="fw-semibold text-success">{{ $tva->date_paiement->format('d/m/Y') }}</div></div>
            @endif
            @if($tva->reference_paiement)
            <div class="col-6"><small class="text-muted">Référence</small><div>{{ $tva->reference_paiement }}</div></div>
            @endif
            @if($tva->notes)
            <div class="col-12"><small class="text-muted">Notes</small><div class="text-muted small">{{ $tva->notes }}</div></div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2 flex-wrap">
            @if($tva->statut === 'brouillon')
            <form action="{{ route('impots.tva.soumettre', $tva) }}" method="POST">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Soumettre à la DGI</button>
            </form>
            @endif

            @if(in_array($tva->statut, ['brouillon', 'soumise', 'en_retard']) && $tva->montant_a_payer > 0)
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPaiement">
                <i class="bi bi-check-circle me-1"></i>Enregistrer le paiement
            </button>
            @endif

            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>Imprimer
            </button>
            <a href="{{ route('impots.tva.index') }}" class="btn btn-outline-secondary">Retour</a>
        </div>
    </div>
</div>

</div>
</div>

{{-- Modal paiement --}}
<div class="modal fade" id="modalPaiement" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Enregistrer le paiement TVA</h5></div>
            <form action="{{ route('impots.tva.payer', $tva) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Référence de paiement <span class="text-danger">*</span></label>
                        <input type="text" name="reference_paiement" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de paiement <span class="text-danger">*</span></label>
                        <input type="date" name="date_paiement" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
