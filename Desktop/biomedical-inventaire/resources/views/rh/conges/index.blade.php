@extends('layouts.app')

@section('title', 'Gestion des congés')
@section('page-title', 'Gestion des congés')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item active">Congés</li>
@endsection

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card text-center p-3 border-warning">
            <div class="fw-bold fs-3 text-warning">{{ $stats['en_attente'] }}</div>
            <div class="text-muted small">Demandes en attente</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-center p-3 border-success">
            <div class="fw-bold fs-3 text-success">{{ $stats['approuves'] }}</div>
            <div class="text-muted small">Approuvés cette année</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card text-center p-3">
            <a href="{{ route('rh.conges.create') }}" class="btn btn-primary w-100 py-3">
                <i class="bi bi-plus-lg fs-5 d-block mb-1"></i>Nouvelle demande
            </a>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="employe_id" class="form-select form-select-sm">
                    <option value="">Tous les employés</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}" @selected(request('employe_id') == $e->id)>{{ $e->nom_complet }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type_conge" class="form-select form-select-sm">
                    <option value="">Tous types</option>
                    <option value="annuel" @selected(request('type_conge')=='annuel')>Congé annuel</option>
                    <option value="maladie" @selected(request('type_conge')=='maladie')>Maladie</option>
                    <option value="maternite" @selected(request('type_conge')=='maternite')>Maternité</option>
                    <option value="paternite" @selected(request('type_conge')=='paternite')>Paternité</option>
                    <option value="sans_solde" @selected(request('type_conge')=='sans_solde')>Sans solde</option>
                    <option value="deuil" @selected(request('type_conge')=='deuil')>Deuil</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="en_attente" @selected(request('statut')=='en_attente')>En attente</option>
                    <option value="approuve" @selected(request('statut')=='approuve')>Approuvé</option>
                    <option value="refuse" @selected(request('statut')=='refuse')>Refusé</option>
                    <option value="annule" @selected(request('statut')=='annule')>Annulé</option>
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
                <a href="{{ route('rh.conges.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
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
                    <th>Type</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th class="text-center">Jours</th>
                    <th>Statut</th>
                    <th>Demandé le</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($conges as $c)
                <tr>
                    <td>
                        <a href="{{ route('rh.employes.show', $c->employe) }}" class="text-decoration-none fw-semibold">
                            {{ $c->employe->nom_complet }}
                        </a>
                    </td>
                    <td>{{ $c->type_label }}</td>
                    <td>{{ $c->date_debut->format('d/m/Y') }}</td>
                    <td>{{ $c->date_fin->format('d/m/Y') }}</td>
                    <td class="text-center fw-semibold">{{ $c->nombre_jours }}</td>
                    <td><span class="badge bg-{{ $c->statut_badge }}">{{ $c->statut_label }}</span></td>
                    <td><small class="text-muted">{{ $c->created_at->format('d/m/Y') }}</small></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('rh.conges.show', $c) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('rh.conges.gerer')
                            @if($c->statut === 'en_attente')
                            <form action="{{ route('rh.conges.approuver', $c) }}" method="POST">
                                @csrf
                                <button class="btn btn-outline-success" title="Approuver">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            <button class="btn btn-outline-danger" title="Refuser"
                                data-bs-toggle="modal" data-bs-target="#modalRefus{{ $c->id }}">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>

                @can('rh.conges.gerer')
                {{-- Modal refus --}}
                <div class="modal fade" id="modalRefus{{ $c->id }}" tabindex="-1">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h6 class="modal-title">Refuser le congé</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('rh.conges.refuser', $c) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <p class="small">{{ $c->employe->nom_complet }} — {{ $c->type_label }} ({{ $c->nombre_jours }} j.)</p>
                                    <label class="form-label">Motif du refus <span class="text-danger">*</span></label>
                                    <textarea name="motif_refus" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-danger w-100">Refuser</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endcan
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>Aucun congé trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($conges->hasPages())
    <div class="card-footer">{{ $conges->links() }}</div>
    @endif
</div>
@endsection
