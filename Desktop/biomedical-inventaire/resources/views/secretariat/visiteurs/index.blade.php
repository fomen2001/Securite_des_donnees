@extends('layouts.app')
@section('title', 'Registre des visiteurs')
@section('page-title', 'Registre des visiteurs')
@section('breadcrumb')
    <li class="breadcrumb-item active">Secrétariat</li>
    <li class="breadcrumb-item active">Visiteurs</li>
@endsection

@section('content')
{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card blue p-3">
            <div class="text-muted small">Aujourd'hui</div>
            <div class="fs-3 fw-bold text-primary">{{ $stats['aujourd_hui'] }}</div>
            <div class="small text-muted">visiteurs</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card orange p-3">
            <div class="text-muted small">En attente</div>
            <div class="fs-3 fw-bold text-warning">{{ $stats['en_attente'] }}</div>
            <div class="small text-muted">à recevoir</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card green p-3">
            <div class="text-muted small">En cours</div>
            <div class="fs-3 fw-bold text-success">{{ $stats['en_cours'] }}</div>
            <div class="small text-muted">dans les locaux</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="text-muted small">Ce mois</div>
            <div class="fs-3 fw-bold">{{ $stats['ce_mois'] }}</div>
            <div class="small text-muted">visiteurs</div>
        </div>
    </div>
</div>

{{-- Filtres + bouton --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form class="row g-2 align-items-center" method="GET">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       placeholder="Nom, entreprise, personne visitée…">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="en_attente" {{ request('statut')=='en_attente'?'selected':'' }}>En attente</option>
                    <option value="recu"       {{ request('statut')=='recu'?'selected':'' }}>Reçu</option>
                    <option value="sorti"      {{ request('statut')=='sorti'?'selected':'' }}>Sorti</option>
                    <option value="annule"     {{ request('statut')=='annule'?'selected':'' }}>Annulé</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="{{ route('secretariat.visiteurs.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Réinitialiser</a>
            </div>
            <div class="col-auto ms-auto">
                @can('secretariat.visiteurs.gerer')
                <a href="{{ route('secretariat.visiteurs.create') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-person-plus me-1"></i>Enregistrer un visiteur
                </a>
                @endcan
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Visiteur</th>
                    <th>Entreprise</th>
                    <th>Objet</th>
                    <th>Personne visitée</th>
                    <th>Entrée</th>
                    <th>Durée</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($visiteurs as $v)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $v->nom_complet }}</div>
                        @if($v->telephone)<div class="small text-muted">{{ $v->telephone }}</div>@endif
                    </td>
                    <td>{{ $v->entreprise ?? '—' }}</td>
                    <td class="small">{{ Str::limit($v->objet_visite, 40) }}</td>
                    <td>{{ $v->personne_visitee }}</td>
                    <td class="small">{{ $v->date_entree->format('d/m/Y H:i') }}</td>
                    <td class="small text-muted">{{ $v->duree ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $v->statut_badge }}">{{ $v->statut_label }}</span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('secretariat.visiteurs.show', $v) }}"
                               class="btn btn-xs btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('secretariat.visiteurs.gerer')
                            @if($v->statut === 'en_attente')
                                <form action="{{ route('secretariat.visiteurs.recevoir', $v) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-xs btn-success" title="Marquer reçu">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            @elseif($v->statut === 'recu')
                                <form action="{{ route('secretariat.visiteurs.sortir', $v) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-xs btn-warning" title="Enregistrer le départ">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </button>
                                </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-people fs-2 d-block mb-2"></i>Aucun visiteur enregistré.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($visiteurs->hasPages())
    <div class="card-footer">{{ $visiteurs->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>.btn-xs{padding:.2rem .5rem;font-size:.75rem;}</style>
@endpush
