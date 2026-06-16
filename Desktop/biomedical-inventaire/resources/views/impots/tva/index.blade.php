@extends('layouts.app')

@section('title', 'Déclarations TVA')
@section('page-title', 'Déclarations de TVA')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item active">TVA</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center p-3 border-success">
            <div class="fw-bold fs-3 text-success">{{ number_format($totalPaye, 0, ',', ' ') }}</div>
            <small class="text-muted">FCFA payés cette année</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-3 border-warning">
            <div class="fw-bold fs-3 text-warning">{{ $enAttente }}</div>
            <small class="text-muted">Déclaration(s) en attente</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-3 {{ $creditTotal > 0 ? 'border-info' : '' }}">
            <a href="{{ route('impots.tva.create') }}" class="btn btn-primary w-100 py-3">
                <i class="bi bi-plus-lg fs-5 d-block mb-1"></i>Nouvelle déclaration
            </a>
        </div>
    </div>
</div>

@if($creditTotal > 0)
<div class="alert alert-info">
    <i class="bi bi-arrow-left-right me-2"></i>
    Crédit TVA reportable : <strong>{{ number_format($creditTotal, 0, ',', ' ') }} FCFA</strong>
    (sera déduit automatiquement à la prochaine déclaration)
</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Période</th>
                    <th>Échéance</th>
                    <th class="text-end">TVA collectée</th>
                    <th class="text-end">TVA déductible</th>
                    <th class="text-end">Crédit antérieur</th>
                    <th class="text-end fw-bold">Montant à payer</th>
                    <th class="text-center">Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($declarations as $d)
                <tr>
                    <td class="fw-semibold">{{ $d->periode_label }}</td>
                    <td>
                        {{ $d->date_echeance->format('d/m/Y') }}
                        @if($d->estEnRetard())
                            <span class="badge bg-danger ms-1">Retard</span>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($d->tva_collectee, 0, ',', ' ') }}</td>
                    <td class="text-end text-muted">{{ number_format($d->tva_deductible, 0, ',', ' ') }}</td>
                    <td class="text-end text-muted">{{ $d->credit_anterieur > 0 ? '-'.number_format($d->credit_anterieur, 0, ',', ' ') : '—' }}</td>
                    <td class="text-end fw-bold {{ $d->montant_a_payer > 0 ? 'text-danger' : 'text-success' }}">
                        @if($d->credit_nouveau > 0)
                            <span class="text-info">Crédit {{ number_format($d->credit_nouveau, 0, ',', ' ') }}</span>
                        @else
                            {{ number_format($d->montant_a_payer, 0, ',', ' ') }} FCFA
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $d->statut_badge }}">{{ $d->statut_label }}</span>
                    </td>
                    <td>
                        <a href="{{ route('impots.tva.show', $d) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-receipt fs-3 d-block mb-2"></i>Aucune déclaration TVA enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($declarations->hasPages())
    <div class="card-footer">{{ $declarations->links() }}</div>
    @endif
</div>
@endsection
