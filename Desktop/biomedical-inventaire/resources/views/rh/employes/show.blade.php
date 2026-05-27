@extends('layouts.app')

@section('title', $employe->nom_complet . ' — Fiche employé')
@section('page-title', $employe->nom_complet)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.employes.index') }}">Employés</a></li>
    <li class="breadcrumb-item active">{{ $employe->matricule }}</li>
@endsection

@section('content')

<div class="row g-4">

    {{-- Carte profil --}}
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">
                    <i class="bi bi-person fs-1 text-primary"></i>
                </div>
                <h5 class="fw-bold mb-0">{{ $employe->nom_complet }}</h5>
                <p class="text-muted mb-1">{{ $employe->poste }}</p>
                <span class="badge bg-{{ ['actif'=>'success','conge'=>'info','suspendu'=>'warning','demissionne'=>'secondary','licencie'=>'danger'][$employe->statut] ?? 'secondary' }} mb-3">
                    {{ $employe->statut_label }}
                </span>
                <div class="d-flex justify-content-center gap-2">
                    @can('rh.employes.modifier')
                    <a href="{{ route('rh.employes.edit', $employe) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </a>
                    @endcan
                    <a href="{{ route('rh.bulletins.create', ['employe_id' => $employe->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-file-earmark-text me-1"></i>Bulletin
                    </a>
                    <a href="{{ route('rh.conges.create', ['employe_id' => $employe->id]) }}" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-calendar-x me-1"></i>Congé
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats rapides --}}
        <div class="row g-3">
            <div class="col-6">
                <div class="card text-center p-3">
                    <div class="fw-bold fs-4 text-primary">{{ $employe->anciennete }}</div>
                    <small class="text-muted">an(s) ancienneté</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card text-center p-3">
                    <div class="fw-bold fs-4 text-success">{{ $employe->solde_conge }}</div>
                    <small class="text-muted">jours de congé</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card text-center p-3">
                    <div class="fw-bold fs-4">{{ $stats['bulletins_count'] }}</div>
                    <small class="text-muted">bulletins</small>
                </div>
            </div>
            <div class="col-6">
                <div class="card text-center p-3">
                    <div class="fw-bold fs-4 text-warning">{{ $stats['conges_en_attente'] }}</div>
                    <small class="text-muted">congés en att.</small>
                </div>
            </div>
        </div>

        {{-- Créditer congé --}}
        @can('rh.conges.gerer')
        <div class="card mt-3">
            <div class="card-body">
                <p class="mb-1 small fw-semibold">Droit congé annuel : <strong>{{ $droitConge }} jours</strong></p>
                <p class="mb-2 small text-muted">Solde actuel : {{ $employe->solde_conge }} jours</p>
                <form action="{{ route('rh.employes.crediter-conge', $employe) }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-outline-success w-100">
                        <i class="bi bi-plus-circle me-1"></i>Créditer {{ $droitConge }} j. de congé
                    </button>
                </form>
            </div>
        </div>
        @endcan
    </div>

    {{-- Détails --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header fw-semibold"><i class="bi bi-person-vcard me-2"></i>Informations personnelles</div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach([
                        ['Matricule', $employe->matricule],
                        ['Date de naissance', $employe->date_naissance?->format('d/m/Y') ?? '—'],
                        ['Lieu de naissance', $employe->lieu_naissance ?? '—'],
                        ['Nationalité', $employe->nationalite],
                        ['Sexe', $employe->sexe === 'M' ? 'Masculin' : 'Féminin'],
                        ['Situation matrimoniale', $employe->situation_label],
                        ['Nombre d\'enfants', $employe->nombre_enfants],
                        ['N° CNI', $employe->numero_cni ?? '—'],
                        ['N° CNPS', $employe->numero_cnps ?? '—'],
                        ['Téléphone', $employe->telephone ?? '—'],
                        ['Email', $employe->email ?? '—'],
                        ['Ville', $employe->ville ?? '—'],
                    ] as [$label, $val])
                    <div class="col-md-6">
                        <div class="d-flex">
                            <span class="text-muted small" style="width:160px;flex-shrink:0">{{ $label }}</span>
                            <span class="fw-semibold small">{{ $val }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header fw-semibold"><i class="bi bi-briefcase me-2"></i>Informations professionnelles</div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach([
                        ['Poste', $employe->poste],
                        ['Département', $employe->departement ?? '—'],
                        ['Service', $employe->service?->nom ?? '—'],
                        ['Catégorie', $employe->categorie_professionnelle ?? '—'],
                        ['Type contrat', $employe->contrat_label],
                        ['Date embauche', $employe->date_embauche->format('d/m/Y')],
                        ['Fin contrat', $employe->date_fin_contrat?->format('d/m/Y') ?? '—'],
                        ['Salaire de base', number_format($employe->salaire_base, 0, ',', ' ') . ' FCFA'],
                        ['Congés pris (année)', $stats['conges_pris_annee'] . ' jours'],
                        ['Droit congé annuel', $droitConge . ' jours'],
                    ] as [$label, $val])
                    <div class="col-md-6">
                        <div class="d-flex">
                            <span class="text-muted small" style="width:160px;flex-shrink:0">{{ $label }}</span>
                            <span class="fw-semibold small">{{ $val }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Derniers bulletins --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-file-earmark-text me-2"></i>Derniers bulletins de paie</span>
                <a href="{{ route('rh.bulletins.index', ['employe_id' => $employe->id]) }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Période</th><th>Brut</th><th>Net à payer</th><th>Statut</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($employe->bulletinsPaie as $b)
                        <tr>
                            <td>{{ $b->periode_label }}</td>
                            <td>{{ number_format($b->salaire_brut, 0, ',', ' ') }} FCFA</td>
                            <td class="fw-semibold text-success">{{ number_format($b->net_a_payer, 0, ',', ' ') }} FCFA</td>
                            <td><span class="badge bg-{{ $b->statut_badge }}">{{ ucfirst($b->statut) }}</span></td>
                            <td><a href="{{ route('rh.bulletins.show', $b) }}" class="btn btn-xs btn-outline-secondary btn-sm"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun bulletin</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Derniers congés --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-calendar-x me-2"></i>Historique des congés</span>
                <a href="{{ route('rh.conges.index', ['employe_id' => $employe->id]) }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Type</th><th>Début</th><th>Fin</th><th>Jours</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @forelse($employe->conges as $c)
                        <tr>
                            <td>{{ $c->type_label }}</td>
                            <td>{{ $c->date_debut->format('d/m/Y') }}</td>
                            <td>{{ $c->date_fin->format('d/m/Y') }}</td>
                            <td>{{ $c->nombre_jours }}</td>
                            <td><span class="badge bg-{{ $c->statut_badge }}">{{ $c->statut_label }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucun congé enregistré</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
