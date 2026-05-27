@extends('layouts.app')

@section('title', 'Module RH — Tableau de bord')
@section('page-title', 'Tableau de bord RH')
@section('breadcrumb')
    <li class="breadcrumb-item active">RH</li>
@endsection

@section('content')

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-people fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4">{{ $stats['employes_actifs'] }}</div>
                    <div class="text-muted small">Employés actifs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-calendar-x fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4">{{ $stats['conges_en_attente'] }}</div>
                    <div class="text-muted small">Congés en attente</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-file-earmark-text fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4">{{ $stats['bulletins_mois'] }}</div>
                    <div class="text-muted small">Bulletins ce mois</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-cash-stack fs-4 text-info"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4">{{ number_format($stats['masse_salariale_mois'], 0, ',', ' ') }}</div>
                    <div class="text-muted small">Masse salariale (FCFA)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Accès rapides --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-lightning me-2"></i>Actions rapides</div>
            <div class="list-group list-group-flush">
                @can('rh.employes.creer')
                <a href="{{ route('rh.employes.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus text-primary"></i> Enregistrer un employé
                </a>
                @endcan
                @can('rh.bulletins.creer')
                <a href="{{ route('rh.bulletins.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-plus text-success"></i> Générer un bulletin de paie
                </a>
                @endcan
                <a href="{{ route('rh.conges.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-plus text-warning"></i> Nouvelle demande de congé
                </a>
                <a href="{{ route('rh.employes.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-people text-secondary"></i> Liste des employés
                </a>
                <a href="{{ route('rh.bulletins.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text text-secondary"></i> Tous les bulletins
                </a>
                <a href="{{ route('rh.conges.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-x text-secondary"></i> Gestion des congés
                </a>
            </div>
        </div>
    </div>

    {{-- Congés en attente --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-x me-2 text-warning"></i>Congés en attente</span>
                <a href="{{ route('rh.conges.index', ['statut' => 'en_attente']) }}" class="btn btn-sm btn-outline-warning">Voir tout</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($congesEnAttente as $c)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold small">{{ $c->employe->nom_complet }}</span>
                        <span class="badge bg-light text-dark">{{ $c->nombre_jours }} j.</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">{{ $c->type_label }} — {{ $c->date_debut->format('d/m') }} au {{ $c->date_fin->format('d/m/Y') }}</small>
                        <div class="btn-group btn-group-sm ms-2">
                            @can('rh.conges.gerer')
                            <form action="{{ route('rh.conges.approuver', $c) }}" method="POST">
                                @csrf
                                <button class="btn btn-xs btn-outline-success btn-sm" title="Approuver"><i class="bi bi-check"></i></button>
                            </form>
                            @endcan
                            <a href="{{ route('rh.conges.show', $c) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="list-group-item text-center text-muted py-4">
                    <i class="bi bi-check-circle text-success fs-4 d-block mb-1"></i>Aucun congé en attente
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Derniers bulletins --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-text me-2 text-success"></i>Derniers bulletins</span>
                <a href="{{ route('rh.bulletins.index') }}" class="btn btn-sm btn-outline-success">Voir tout</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($derniersBulletins as $b)
                <a href="{{ route('rh.bulletins.show', $b) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold small">{{ $b->employe->nom_complet }}</span>
                        <span class="badge bg-{{ $b->statut_badge }}">{{ ucfirst($b->statut) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">{{ $b->periode_label }}</small>
                        <small class="fw-semibold text-success">{{ number_format($b->net_a_payer, 0, ',', ' ') }} FCFA</small>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-center text-muted py-4">
                    <i class="bi bi-file-earmark fs-4 d-block mb-1"></i>Aucun bulletin ce mois
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Récap cotisations légales --}}
<div class="card mt-4">
    <div class="card-header fw-semibold"><i class="bi bi-info-circle me-2"></i>Rappel des taux légaux en vigueur — Cameroun</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="fw-bold text-primary">CNPS Salarié</div>
                    <div class="fs-4 fw-bold">4,20 %</div>
                    <small class="text-muted">Vieillesse / Invalidité / Décès<br>Plafond : 750 000 FCFA/mois</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="fw-bold text-info">CNPS Employeur</div>
                    <div class="fs-4 fw-bold">~12,95 %</div>
                    <small class="text-muted">Vieillesse (4,2 %) + AF (7 %) + AT (1,75 %)<br>Plafond : 750 000 FCFA/mois</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="fw-bold text-warning">IRPP + CAC</div>
                    <div class="fs-4 fw-bold">Progressif</div>
                    <small class="text-muted">10 % → 38,5 % (barème annuel)<br>CAC = 10 % de l'IRPP</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="fw-bold text-secondary">SMIG & RAV</div>
                    <div class="fs-4 fw-bold">41 875 FCFA</div>
                    <small class="text-muted">SMIG mensuel (référence)<br>RAV : 2 500 FCFA/mois</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
