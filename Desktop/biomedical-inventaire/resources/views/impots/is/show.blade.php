@extends('layouts.app')

@section('title', $is->type_label)
@section('page-title', $is->type_label)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item"><a href="{{ route('impots.is.index') }}">IS</a></li>
    <li class="breadcrumb-item active">{{ $is->type_label }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-bank me-2"></i>{{ $is->type_label }}</span>
        <span class="badge bg-{{ $is->statut_badge }} fs-6">{{ $is->statut_label }}</span>
    </div>
    <div class="card-body">
        <table class="table table-bordered mb-4">
            <tbody>
                <tr><td class="text-muted">Type</td><td class="fw-semibold">{{ $is->type === 'acompte' ? 'Acompte provisionnel (T'.$is->trimestre.')' : 'Déclaration annuelle' }}</td></tr>
                <tr><td class="text-muted">Échéance</td><td>{{ $is->date_echeance->format('d/m/Y') }}</td></tr>
                <tr class="table-light"><td class="fw-semibold">Chiffre d'affaires</td><td class="text-end fw-semibold">{{ number_format($is->chiffre_affaires, 0, ',', ' ') }} FCFA</td></tr>
                <tr><td class="text-muted">Bénéfice imposable</td><td class="text-end">{{ number_format($is->benefice_imposable, 0, ',', ' ') }} FCFA</td></tr>
                <tr><td class="text-muted">IS brut (30 %)</td><td class="text-end">{{ number_format($is->is_brut, 0, ',', ' ') }} FCFA</td></tr>
                <tr><td class="text-muted">Minimum forfaitaire (1 % CA)</td><td class="text-end">{{ number_format($is->minimum_is, 0, ',', ' ') }} FCFA</td></tr>
                <tr class="table-warning"><td class="fw-semibold">IS dû</td><td class="text-end fw-bold">{{ number_format($is->is_du, 0, ',', ' ') }} FCFA</td></tr>
                @if($is->type === 'acompte')
                <tr class="table-danger"><td class="fw-bold">Montant acompte (IS N-1 ÷ 4)</td><td class="text-end fw-bold text-danger">{{ number_format($is->montant_acompte, 0, ',', ' ') }} FCFA</td></tr>
                @else
                <tr><td class="text-muted">Acomptes versés</td><td class="text-end">− {{ number_format($is->acomptes_verses, 0, ',', ' ') }} FCFA</td></tr>
                <tr class="table-danger"><td class="fw-bold">Complément à payer</td><td class="text-end fw-bold text-danger">{{ number_format($is->montant_a_payer, 0, ',', ' ') }} FCFA</td></tr>
                @endif
                @if($is->date_paiement)
                <tr class="table-success"><td class="text-muted">Date de paiement</td><td class="fw-semibold text-success">{{ $is->date_paiement->format('d/m/Y') }}</td></tr>
                @endif
                @if($is->reference_paiement)
                <tr><td class="text-muted">Référence</td><td>{{ $is->reference_paiement }}</td></tr>
                @endif
            </tbody>
        </table>

        @if($is->notes)
        <p class="text-muted small">{{ $is->notes }}</p>
        @endif

        <div class="d-flex gap-2 flex-wrap">
            @if($is->statut === 'brouillon')
            <form action="{{ route('impots.is.soumettre', $is) }}" method="POST">
                @csrf <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Soumettre</button>
            </form>
            @endif
            @if(in_array($is->statut, ['brouillon', 'soumise', 'en_retard']))
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPaiement">
                <i class="bi bi-check-circle me-1"></i>Enregistrer le paiement
            </button>
            @endif
            <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Imprimer</button>
            <a href="{{ route('impots.is.index') }}" class="btn btn-outline-secondary">Retour</a>
        </div>
    </div>
</div>
</div>
</div>

<div class="modal fade" id="modalPaiement" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Paiement IS</h5></div>
            <form action="{{ route('impots.is.payer', $is) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Référence <span class="text-danger">*</span></label>
                        <input type="text" name="reference_paiement" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
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
