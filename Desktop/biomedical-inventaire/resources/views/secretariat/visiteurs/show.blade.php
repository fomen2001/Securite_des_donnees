@extends('layouts.app')
@section('title', 'Visiteur — ' . $visiteur->nom_complet)
@section('page-title', 'Fiche visiteur')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('secretariat.visiteurs.index') }}">Visiteurs</a></li>
    <li class="breadcrumb-item active">{{ $visiteur->nom_complet }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-semibold"><i class="bi bi-person-badge me-2"></i>Identité</span>
                <span class="badge bg-{{ $visiteur->statut_badge }} fs-6">{{ $visiteur->statut_label }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Nom complet</div>
                        <div class="fw-semibold fs-5">{{ $visiteur->nom_complet }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Entreprise</div>
                        <div>{{ $visiteur->entreprise ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Téléphone</div>
                        <div>{{ $visiteur->telephone ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Email</div>
                        <div>{{ $visiteur->email ?? '—' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Objet de la visite</div>
                        <div class="fw-semibold">{{ $visiteur->objet_visite }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Personne visitée</div>
                        <div>{{ $visiteur->personne_visitee }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Badge N°</div>
                        <div>{{ $visiteur->badge_numero ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($visiteur->observations)
        <div class="card mb-4">
            <div class="card-header fw-semibold"><i class="bi bi-chat-text me-2"></i>Observations</div>
            <div class="card-body">{{ $visiteur->observations }}</div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        {{-- Horodatage --}}
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-clock-history me-2"></i>Horodatage</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="small text-muted">Entrée</div>
                    <div class="fw-bold text-success">
                        <i class="bi bi-door-open me-1"></i>{{ $visiteur->date_entree->format('d/m/Y à H:i') }}
                    </div>
                </div>
                <div class="mb-3">
                    <div class="small text-muted">Sortie</div>
                    <div class="fw-bold {{ $visiteur->date_sortie ? 'text-danger' : 'text-muted' }}">
                        <i class="bi bi-door-closed me-1"></i>
                        {{ $visiteur->date_sortie ? $visiteur->date_sortie->format('d/m/Y à H:i') : '—' }}
                    </div>
                </div>
                @if($visiteur->duree)
                <div class="alert alert-light py-2 text-center mb-0">
                    <i class="bi bi-hourglass-split me-1"></i>
                    Durée totale : <strong>{{ $visiteur->duree }}</strong>
                </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        @can('secretariat.visiteurs.gerer')
        <div class="card">
            <div class="card-header fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                @if($visiteur->statut === 'en_attente')
                <form action="{{ route('secretariat.visiteurs.recevoir', $visiteur) }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="btn btn-success w-100">
                        <i class="bi bi-check-circle me-1"></i>Marquer comme reçu
                    </button>
                </form>
                @endif
                @if($visiteur->statut === 'recu')
                <form action="{{ route('secretariat.visiteurs.sortir', $visiteur) }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="btn btn-warning w-100">
                        <i class="bi bi-box-arrow-right me-1"></i>Enregistrer le départ
                    </button>
                </form>
                @endif
                @if(in_array($visiteur->statut, ['en_attente', 'recu']))
                <form action="{{ route('secretariat.visiteurs.annuler', $visiteur) }}" method="POST"
                      onsubmit="return confirm('Annuler cette visite ?')">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-danger w-100">
                        <i class="bi bi-x-circle me-1"></i>Annuler la visite
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endcan

        <div class="text-muted small mt-3">
            Enregistré par <strong>{{ $visiteur->user->name }}</strong><br>
            le {{ $visiteur->created_at->format('d/m/Y à H:i') }}
        </div>
    </div>
</div>
@endsection
