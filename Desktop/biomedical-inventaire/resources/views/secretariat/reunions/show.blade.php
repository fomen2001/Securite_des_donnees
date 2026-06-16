@extends('layouts.app')
@section('title', $reunion->titre)
@section('page-title', $reunion->titre)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('secretariat.reunions.index') }}">Réunions</a></li>
    <li class="breadcrumb-item active">{{ $reunion->reference }}</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">

        {{-- En-tête réunion --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">{{ $reunion->titre }}</h5>
                        <span class="badge bg-{{ $reunion->type_badge }} me-1">{{ $reunion->type_label }}</span>
                        @if($reunion->statut === 'finalise')
                            <span class="badge bg-success">Finalisé</span>
                        @else
                            <span class="badge bg-secondary">Brouillon</span>
                        @endif
                    </div>
                    <span class="font-monospace small text-muted">{{ $reunion->reference }}</span>
                </div>
                <div class="row g-2 text-center">
                    <div class="col-md-4">
                        <div class="border rounded p-2">
                            <div class="small text-muted">Date</div>
                            <div class="fw-bold">{{ $reunion->date_reunion->format('d/m/Y') }}</div>
                            <div class="small text-muted">{{ $reunion->date_reunion->format('H:i') }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-2">
                            <div class="small text-muted">Lieu</div>
                            <div class="fw-bold">{{ $reunion->lieu ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-2">
                            <div class="small text-muted">Participants présents</div>
                            <div class="fw-bold fs-4 text-primary">{{ $reunion->nb_present }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($reunion->ordre_du_jour)
        <div class="card mb-4">
            <div class="card-header fw-semibold"><i class="bi bi-list-ol me-2 text-warning"></i>Ordre du jour</div>
            <div class="card-body" style="white-space:pre-wrap">{{ $reunion->ordre_du_jour }}</div>
        </div>
        @endif

        <div class="card mb-4">
            <div class="card-header fw-semibold"><i class="bi bi-journal-text me-2 text-success"></i>Compte-rendu</div>
            <div class="card-body" style="white-space:pre-wrap;line-height:1.8">{{ $reunion->compte_rendu }}</div>
        </div>

        @if($reunion->decisions)
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-check2-square me-2 text-primary"></i>Décisions prises
            </div>
            <div class="card-body" style="white-space:pre-wrap">{{ $reunion->decisions }}</div>
        </div>
        @endif

        @if($reunion->actions_a_suivre)
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-arrow-right-circle me-2 text-danger"></i>Actions à suivre
            </div>
            <div class="card-body" style="white-space:pre-wrap">{{ $reunion->actions_a_suivre }}</div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        {{-- Participants --}}
        <div class="card mb-3">
            <div class="card-header fw-semibold">
                <i class="bi bi-people me-2"></i>Participants ({{ $reunion->participants->count() }})
            </div>
            <ul class="list-group list-group-flush">
                @foreach($reunion->participants as $p)
                <li class="list-group-item py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold small">{{ $p->nom }}</div>
                            @if($p->fonction || $p->entreprise)
                            <div class="text-muted" style="font-size:.75rem">
                                {{ $p->fonction }}@if($p->fonction && $p->entreprise) — @endif{{ $p->entreprise }}
                            </div>
                            @endif
                        </div>
                        @if($p->present)
                            <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle"></i> Présent</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle"></i> Absent</span>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Actions --}}
        <div class="card mb-3">
            <div class="card-header fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('secretariat.reunions.imprimer', $reunion) }}" target="_blank"
                   class="btn btn-outline-dark">
                    <i class="bi bi-printer me-1"></i>Imprimer / PDF
                </a>
                @can('secretariat.reunions.gerer')
                <a href="{{ route('secretariat.reunions.edit', $reunion) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <form action="{{ route('secretariat.reunions.destroy', $reunion) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce rapport ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
                @endcan
            </div>
        </div>

        <div class="text-muted small">
            Rédigé par <strong>{{ $reunion->user->name }}</strong><br>
            le {{ $reunion->created_at->format('d/m/Y à H:i') }}
        </div>
    </div>
</div>
@endsection
