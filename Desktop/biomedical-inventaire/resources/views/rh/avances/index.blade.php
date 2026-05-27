@extends('layouts.app')

@section('title', 'Avances sur salaire')
@section('page-title', 'Avances sur salaire')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item active">Avances sur salaire</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center p-3 border-warning">
            <div class="fw-bold fs-3 text-warning">{{ $stats['en_attente'] }}</div>
            <small class="text-muted">En attente d'approbation</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-3 border-primary">
            <div class="fw-bold fs-3 text-primary">{{ number_format($stats['total_approuvees'], 0, ',', ' ') }}</div>
            <small class="text-muted">FCFA en cours</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-3">
            <a href="{{ route('rh.avances.create') }}" class="btn btn-primary w-100 py-3">
                <i class="bi bi-plus-lg fs-5 d-block mb-1"></i>Nouvelle demande
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <select name="employe_id" class="form-select form-select-sm">
                    <option value="">Tous les employés</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}" @selected(request('employe_id') == $e->id)>{{ $e->nom_complet }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="en_attente" @selected(request('statut')=='en_attente')>En attente</option>
                    <option value="approuvee" @selected(request('statut')=='approuvee')>Approuvée</option>
                    <option value="remboursee" @selected(request('statut')=='remboursee')>Remboursée</option>
                    <option value="annulee" @selected(request('statut')=='annulee')>Annulée</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="annee" class="form-select form-select-sm">
                    <option value="">Toutes années</option>
                    @foreach($annees as $a)
                        <option value="{{ $a }}" @selected(request('annee') == $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-sm btn-secondary flex-fill"><i class="bi bi-search"></i> Filtrer</button>
                <a href="{{ route('rh.avances.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Employé</th>
                    <th class="text-end">Montant</th>
                    <th>Date avance</th>
                    <th>Déduction prévue</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($avances as $a)
                <tr>
                    <td>
                        <a href="{{ route('rh.employes.show', $a->employe) }}" class="fw-semibold text-decoration-none">
                            {{ $a->employe->nom_complet }}
                        </a>
                    </td>
                    <td class="text-end fw-semibold">{{ number_format($a->montant, 0, ',', ' ') }} FCFA</td>
                    <td>{{ $a->date_avance->format('d/m/Y') }}</td>
                    <td>{{ $a->mois_nom }}</td>
                    <td><small class="text-muted">{{ Str::limit($a->motif, 40) ?? '—' }}</small></td>
                    <td><span class="badge bg-{{ $a->statut_badge }}">{{ $a->statut_label }}</span></td>
                    <td>
                        @can('rh.conges.gerer')
                        <div class="btn-group btn-group-sm">
                            @if($a->statut === 'en_attente')
                            <form action="{{ route('rh.avances.approuver', $a) }}" method="POST">
                                @csrf
                                <button class="btn btn-outline-success" title="Approuver"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <form action="{{ route('rh.avances.annuler', $a) }}" method="POST"
                                  onsubmit="return confirm('Annuler cette avance ?')">
                                @csrf
                                <button class="btn btn-outline-danger" title="Annuler"><i class="bi bi-x-lg"></i></button>
                            </form>
                            @endif
                        </div>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-cash fs-3 d-block mb-2"></i>Aucune avance trouvée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($avances->hasPages())
    <div class="card-footer">{{ $avances->links() }}</div>
    @endif
</div>
@endsection
