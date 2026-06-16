@extends('layouts.app')

@section('title', 'Impôt sur les Sociétés')
@section('page-title', 'Impôt sur les Sociétés (IS)')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item active">IS</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex gap-3">
        <div class="text-center px-3">
            <div class="fw-bold text-success fs-5">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</div>
            <small class="text-muted">IS payé cette année</small>
        </div>
        @if($enRetard > 0)
        <div class="text-center px-3">
            <div class="fw-bold text-danger fs-5">{{ $enRetard }}</div>
            <small class="text-muted">Déclaration(s) en retard</small>
        </div>
        @endif
    </div>
    <a href="{{ route('impots.is.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle déclaration</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Déclaration</th>
                    <th>Échéance</th>
                    <th class="text-end">CA</th>
                    <th class="text-end">IS brut (30 %)</th>
                    <th class="text-end">Minimum IS</th>
                    <th class="text-end fw-bold">Montant dû</th>
                    <th class="text-center">Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($declarations as $d)
                <tr>
                    <td>
                        <span class="fw-semibold">{{ $d->type_label }}</span>
                        @if($d->type === 'acompte')
                            <span class="badge bg-light text-dark ms-1">Acompte</span>
                        @else
                            <span class="badge bg-info ms-1">Annuelle</span>
                        @endif
                    </td>
                    <td>
                        {{ $d->date_echeance->format('d/m/Y') }}
                        @if($d->estEnRetard())<span class="badge bg-danger ms-1">Retard</span>@endif
                    </td>
                    <td class="text-end">{{ number_format($d->chiffre_affaires, 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($d->is_brut, 0, ',', ' ') }}</td>
                    <td class="text-end text-muted">{{ number_format($d->minimum_is, 0, ',', ' ') }}</td>
                    <td class="text-end fw-bold text-danger">{{ number_format($d->montant_a_payer, 0, ',', ' ') }} FCFA</td>
                    <td class="text-center"><span class="badge bg-{{ $d->statut_badge }}">{{ $d->statut_label }}</span></td>
                    <td>
                        <a href="{{ route('impots.is.show', $d) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-bank fs-3 d-block mb-2"></i>Aucune déclaration IS enregistrée.
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

<div class="card mt-4 border-info">
    <div class="card-header small fw-semibold text-info"><i class="bi bi-info-circle me-2"></i>Rappel IS — CGI Cameroun</div>
    <div class="card-body small">
        <div class="row g-2">
            <div class="col-md-4"><strong>Taux</strong> : 30 % du bénéfice imposable</div>
            <div class="col-md-4"><strong>Minimum forfaitaire</strong> : 1 % du CA (plancher 500 000 FCFA)</div>
            <div class="col-md-4"><strong>IS dû</strong> = max(IS 30 %, minimum forfaitaire)</div>
            <div class="col-md-6"><strong>Acomptes</strong> : 4 × 25 % de l'IS N-1 — 15 Fév / 15 Mai / 15 Août / 15 Nov</div>
            <div class="col-md-6"><strong>DSF</strong> : Déclaration annuelle avant le 15 Avril N+1</div>
        </div>
    </div>
</div>
@endsection
