@extends('layouts.app')

@section('title', $document->titre)
@section('page-title', $document->titre)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
    <li class="breadcrumb-item active">{{ $document->reference }}</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- Détail principal --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi {{ $document->icone_mime }} fs-4"></i>
                    <div>
                        <div class="fw-semibold">{{ $document->titre }}</div>
                        <div class="text-muted small">{{ $document->reference }}</div>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <span class="badge bg-{{ $document->statut_badge }}">{{ $document->statut_label }}</span>
                    <span class="badge bg-{{ $document->confidentialite_badge }}">
                        <i class="bi bi-{{ $document->confidentialite === 'confidentiel' ? 'lock' : ($document->confidentialite === 'interne' ? 'building' : 'globe') }} me-1"></i>
                        {{ $document->confidentialite_label }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Catégorie</div>
                        <div class="fw-semibold">
                            <i class="{{ $document->categorie->icone }} me-1 text-{{ $document->categorie->couleur }}"></i>
                            {{ $document->categorie->nom }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Type</div>
                        <div class="fw-semibold">{{ $document->type_label }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Date du document</div>
                        <div class="fw-semibold">{{ $document->date_document->format('d/m/Y') }}</div>
                    </div>

                    @if($document->date_expiration)
                    <div class="col-md-4">
                        <div class="text-muted small">Date d'expiration</div>
                        <div class="fw-semibold {{ $document->est_expire ? 'text-danger' : ($document->expire_bientot ? 'text-warning' : '') }}">
                            {{ $document->date_expiration->format('d/m/Y') }}
                            @if($document->est_expire)
                                <span class="badge bg-danger ms-1">Expiré</span>
                            @elseif($document->expire_bientot)
                                <span class="badge bg-warning text-dark ms-1">{{ $document->jours_avant_expiration }}j restants</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="col-md-4">
                        <div class="text-muted small">Ajouté par</div>
                        <div class="fw-semibold">{{ $document->user->name }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Date d'ajout</div>
                        <div class="fw-semibold">{{ $document->created_at->format('d/m/Y') }}</div>
                    </div>

                    @if($document->description)
                    <div class="col-12">
                        <div class="text-muted small">Description</div>
                        <div class="mt-1">{{ $document->description }}</div>
                    </div>
                    @endif

                    @if($document->tags_list)
                    <div class="col-12">
                        <div class="text-muted small mb-1">Mots-clés</div>
                        @foreach($document->tags_list as $tag)
                        <span class="badge bg-light text-dark border me-1 mb-1">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Fichier --}}
        <div class="card mt-3">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-light rounded-3 p-3">
                    <i class="bi {{ $document->icone_mime }} fs-2"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $document->fichier_nom_original }}</div>
                    <div class="text-muted small">{{ strtoupper($document->fichier_mime) }} · {{ $document->taille_lisible }}</div>
                    <div class="text-muted small"><i class="bi bi-download me-1"></i>{{ $document->telechargements }} téléchargement(s)</div>
                </div>
                <a href="{{ route('documents.download', $document) }}" class="btn btn-success">
                    <i class="bi bi-download me-1"></i>Télécharger
                </a>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('documents.download', $document) }}" class="btn btn-success">
                    <i class="bi bi-download me-1"></i>Télécharger le fichier
                </a>

                @can('documents.modifier')
                <a href="{{ route('documents.edit', $document) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>

                @if($document->statut !== 'archive')
                <form action="{{ route('documents.archiver', $document) }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-secondary w-100">
                        <i class="bi bi-archive me-1"></i>Archiver
                    </button>
                </form>
                @else
                <form action="{{ route('documents.restaurer', $document) }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-success w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Restaurer
                    </button>
                </form>
                @endif
                @endcan

                @can('documents.supprimer')
                <form action="{{ route('documents.destroy', $document) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce document et son fichier définitivement ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
                @endcan

                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
