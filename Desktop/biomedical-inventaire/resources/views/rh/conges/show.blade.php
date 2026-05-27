@extends('layouts.app')

@section('title', 'Congé — ' . $conge->employe->nom_complet)
@section('page-title', 'Détail du congé')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.conges.index') }}">Congés</a></li>
    <li class="breadcrumb-item active">#{{ $conge->id }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Demande de congé</span>
                <span class="badge bg-{{ $conge->statut_badge }} fs-6">{{ $conge->statut_label }}</span>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr><th style="width:180px">Employé</th><td><a href="{{ route('rh.employes.show', $conge->employe) }}" class="fw-semibold">{{ $conge->employe->nom_complet }}</a></td></tr>
                    <tr><th>Type de congé</th><td>{{ $conge->type_label }}</td></tr>
                    <tr><th>Date de début</th><td>{{ $conge->date_debut->format('d/m/Y') }}</td></tr>
                    <tr><th>Date de fin</th><td>{{ $conge->date_fin->format('d/m/Y') }}</td></tr>
                    <tr><th>Nombre de jours</th><td class="fw-bold">{{ $conge->nombre_jours }} jour(s) ouvrable(s)</td></tr>
                    <tr><th>Motif</th><td>{{ $conge->motif ?? '—' }}</td></tr>
                    <tr><th>Solde avant</th><td>{{ $conge->solde_avant }} jour(s)</td></tr>
                    <tr><th>Solde après</th><td>{{ $conge->solde_apres }} jour(s)</td></tr>
                    <tr><th>Demandé le</th><td>{{ $conge->created_at->format('d/m/Y à H:i') }}</td></tr>
                    @if($conge->approbateur)
                    <tr><th>{{ $conge->statut === 'approuve' ? 'Approuvé' : 'Traité' }} par</th><td>{{ $conge->approbateur->name }}</td></tr>
                    <tr><th>Date traitement</th><td>{{ $conge->date_approbation?->format('d/m/Y à H:i') }}</td></tr>
                    @endif
                    @if($conge->motif_refus)
                    <tr><th>Motif de refus</th><td class="text-danger">{{ $conge->motif_refus }}</td></tr>
                    @endif
                </table>

                <div class="d-flex gap-2 mt-3">
                    @can('rh.conges.gerer')
                    @if($conge->statut === 'en_attente')
                    <form action="{{ route('rh.conges.approuver', $conge) }}" method="POST">
                        @csrf
                        <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approuver</button>
                    </form>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalRefus">
                        <i class="bi bi-x-lg me-1"></i>Refuser
                    </button>
                    @endif
                    @if(in_array($conge->statut, ['en_attente', 'approuve']))
                    <form action="{{ route('rh.conges.annuler', $conge) }}" method="POST"
                          onsubmit="return confirm('Annuler ce congé ?')">
                        @csrf
                        <button class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Annuler</button>
                    </form>
                    @endif
                    @endcan
                    <a href="{{ route('rh.conges.index') }}" class="btn btn-outline-secondary ms-auto">Retour</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal refus --}}
@can('rh.conges.gerer')
<div class="modal fade" id="modalRefus" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Motif du refus</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rh.conges.refuser', $conge) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <textarea name="motif_refus" class="form-control" rows="3" required placeholder="Expliquez le motif de refus..."></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger w-100">Confirmer le refus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
