@extends('layouts.app')

@section('title', 'Patente')
@section('page-title', 'Patente — Taxe professionnelle')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item active">Patente</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Renouvellement annuel avant le <strong>31 mars</strong></p>
    <a href="{{ route('impots.patente.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle patente</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Année</th>
                    <th>Échéance</th>
                    <th class="text-end">CA référence</th>
                    <th class="text-end">Droit fixe</th>
                    <th class="text-end">Droit variable</th>
                    <th class="text-end">CAC (10 %)</th>
                    <th class="text-end fw-bold">Total</th>
                    <th class="text-center">Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($patentes as $p)
                <tr>
                    <td class="fw-bold fs-5">{{ $p->annee }}</td>
                    <td>
                        {{ $p->date_echeance->format('d/m/Y') }}
                        @if($p->estEnRetard())<span class="badge bg-danger ms-1">Retard</span>@endif
                    </td>
                    <td class="text-end">{{ number_format($p->chiffre_affaires_reference, 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($p->droit_fixe, 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($p->droit_variable, 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($p->centimes_additionnels, 0, ',', ' ') }}</td>
                    <td class="text-end fw-bold text-danger">{{ number_format($p->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td class="text-center"><span class="badge bg-{{ $p->statut_badge }}">{{ $p->statut_label }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if($p->statut === 'brouillon')
                            <form action="{{ route('impots.patente.soumettre', $p) }}" method="POST">
                                @csrf <button class="btn btn-outline-primary" title="Soumettre"><i class="bi bi-send"></i></button>
                            </form>
                            @endif
                            @if(in_array($p->statut, ['brouillon','soumise','en_retard']))
                            <button class="btn btn-outline-success"
                                    data-bs-toggle="modal" data-bs-target="#modalPay{{ $p->id }}"
                                    title="Marquer payée"><i class="bi bi-check-lg"></i></button>
                            @endif
                        </div>

                        {{-- Modal paiement --}}
                        <div class="modal fade" id="modalPay{{ $p->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title">Paiement patente {{ $p->annee }}</h5></div>
                                    <form action="{{ route('impots.patente.payer', $p) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-2"><label class="form-label small">Référence paiement <span class="text-danger">*</span></label>
                                                <input type="text" name="reference_paiement" class="form-control form-control-sm" required></div>
                                            <div class="mb-2"><label class="form-label small">N° quittance</label>
                                                <input type="text" name="numero_quittance" class="form-control form-control-sm"></div>
                                            <div class="mb-2"><label class="form-label small">Date <span class="text-danger">*</span></label>
                                                <input type="date" name="date_paiement" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required></div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-sm btn-success">Confirmer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-building fs-3 d-block mb-2"></i>Aucune patente enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($patentes->hasPages())
    <div class="card-footer">{{ $patentes->links() }}</div>
    @endif
</div>

<div class="card mt-4 border-info">
    <div class="card-header small fw-semibold text-info"><i class="bi bi-info-circle me-2"></i>Calcul de la patente — CGI Cameroun</div>
    <div class="card-body small">
        <div class="row g-2">
            <div class="col-md-4"><strong>Base</strong> : CA de l'année précédente</div>
            <div class="col-md-4"><strong>Droit variable</strong> : 0,159 % du CA</div>
            <div class="col-md-4"><strong>CAC</strong> : 10 % × (droit fixe + droit variable)</div>
            <div class="col-12">
                <strong>Droit fixe :</strong>
                CA ≤ 5M : 0 FCFA (régime forfaitaire) |
                5-10M : 60 000 | 10-30M : 80 000 | 30-100M : 150 000 |
                100-500M : 250 000 | 500M-1B : 500 000 | &gt;1B : 1 000 000 FCFA
            </div>
        </div>
    </div>
</div>
@endsection
