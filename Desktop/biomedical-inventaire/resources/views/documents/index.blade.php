@extends('layouts.app')

@section('title', 'GED — Documents')
@section('page-title', 'Gestion documentaire')
@section('breadcrumb')
    <li class="breadcrumb-item active">Documents</li>
@endsection

@push('styles')
<style>
.doc-card { transition: transform .15s, box-shadow .15s; }
.doc-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }
.tag-badge { font-size: .72rem; }
.cat-pill { cursor: pointer; }
</style>
@endpush

@section('content')

{{-- Alertes expiration --}}
@if($alertes->count())
<div class="alert alert-warning alert-dismissible fade show mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>{{ $alertes->count() }} document(s)</strong> expirent dans les 30 prochains jours :
    <ul class="mb-0 mt-1">
        @foreach($alertes->take(5) as $a)
        <li>
            <a href="{{ route('documents.show', $a) }}" class="text-warning-emphasis fw-semibold">{{ $a->titre }}</a>
            — expire le {{ $a->date_expiration->format('d/m/Y') }}
            <span class="badge bg-warning text-dark ms-1">{{ $a->jours_avant_expiration }}j</span>
        </li>
        @endforeach
        @if($alertes->count() > 5)
        <li class="text-muted small">… et {{ $alertes->count() - 5 }} autre(s)</li>
        @endif
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-1 fw-bold text-primary">{{ $stats['total'] }}</div>
            <div class="text-muted small">Documents total</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-1 fw-bold text-success">{{ $stats['actifs'] }}</div>
            <div class="text-muted small">Actifs</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-1 fw-bold text-danger">{{ $stats['expires'] }}</div>
            <div class="text-muted small">Expirés</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-1 fw-bold text-info">{{ number_format($stats['taille'] / 1048576, 1) }} Mo</div>
            <div class="text-muted small">Espace utilisé</div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Sidebar catégories --}}
    <div class="col-lg-3">
        <div class="card mb-3">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-folder2 me-1"></i>Catégories</span>
                @can('documents.gerer')
                <a href="{{ route('documents.categories') }}" class="btn btn-xs btn-outline-secondary btn-sm">
                    <i class="bi bi-gear"></i>
                </a>
                @endcan
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('documents.index') }}"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center cat-pill
                          {{ !request('categorie_id') ? 'active' : '' }}">
                    Tous
                    <span class="badge bg-secondary rounded-pill">{{ $stats['total'] }}</span>
                </a>
                @foreach($categories as $cat)
                <a href="{{ route('documents.index', ['categorie_id' => $cat->id] + request()->except('page')) }}"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center cat-pill
                          {{ request('categorie_id') == $cat->id ? 'active' : '' }}">
                    <span><i class="{{ $cat->icone }} me-1 text-{{ $cat->couleur }}"></i>{{ $cat->nom }}</span>
                    <span class="badge bg-{{ $cat->couleur }} rounded-pill">{{ $cat->documents_count }}</span>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Filtres rapides --}}
        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-funnel me-1"></i>Filtres</div>
            <div class="card-body">
                <form method="GET" id="form-filtres">
                    <input type="hidden" name="categorie_id" value="{{ request('categorie_id') }}">
                    <div class="mb-2">
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="form-control form-control-sm" placeholder="Rechercher…">
                    </div>
                    <div class="mb-2">
                        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tous les types</option>
                            @foreach(['contrat'=>'Contrat','attestation'=>'Attestation','facture'=>'Facture','licence'=>'Licence','rapport'=>'Rapport','proces_verbal'=>'Procès-verbal','convention'=>'Convention','autre'=>'Autre'] as $val => $lbl)
                            <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="statut" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="archive" {{ request('statut') === 'archive' ? 'selected' : '' }}>Archivé</option>
                            <option value="expire" {{ request('statut') === 'expire' ? 'selected' : '' }}>Expiré</option>
                        </select>
                    </div>
                    @can('documents.confidentiels')
                    <div class="mb-2">
                        <select name="confidentialite" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Toutes confidentialités</option>
                            <option value="public" {{ request('confidentialite') === 'public' ? 'selected' : '' }}>Public</option>
                            <option value="interne" {{ request('confidentialite') === 'interne' ? 'selected' : '' }}>Interne</option>
                            <option value="confidentiel" {{ request('confidentialite') === 'confidentiel' ? 'selected' : '' }}>Confidentiel</option>
                        </select>
                    </div>
                    @endcan
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-primary flex-grow-1">Filtrer</button>
                        <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-secondary">✕</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Liste des documents --}}
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted small">{{ $documents->total() }} document(s) trouvé(s)</span>
            @can('documents.creer')
            <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-upload me-1"></i>Ajouter un document
            </a>
            @endcan
        </div>

        @forelse($documents as $doc)
        <div class="card doc-card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center g-3">

                    {{-- Icône fichier --}}
                    <div class="col-auto">
                        <div class="bg-light rounded-3 p-3 text-center" style="width:56px;height:56px;line-height:32px">
                            <i class="bi {{ $doc->icone_mime }} fs-4"></i>
                        </div>
                    </div>

                    {{-- Infos principales --}}
                    <div class="col">
                        <div class="d-flex align-items-start gap-2 flex-wrap mb-1">
                            <a href="{{ route('documents.show', $doc) }}" class="fw-semibold text-decoration-none fs-6">
                                {{ $doc->titre }}
                            </a>
                            <span class="badge bg-{{ $doc->statut_badge }}">{{ $doc->statut_label }}</span>
                            <span class="badge bg-{{ $doc->confidentialite_badge }}">
                                <i class="bi bi-{{ $doc->confidentialite === 'confidentiel' ? 'lock' : ($doc->confidentialite === 'interne' ? 'building' : 'globe') }} me-1"></i>
                                {{ $doc->confidentialite_label }}
                            </span>
                        </div>
                        <div class="text-muted small mb-1">
                            <span class="me-3"><i class="bi bi-hash me-1"></i>{{ $doc->reference }}</span>
                            <span class="me-3"><i class="bi bi-folder me-1"></i>{{ $doc->categorie->nom }}</span>
                            <span class="me-3"><i class="bi bi-tag me-1"></i>{{ $doc->type_label }}</span>
                            <span><i class="bi bi-calendar3 me-1"></i>{{ $doc->date_document->format('d/m/Y') }}</span>
                        </div>
                        @if($doc->tags_list)
                        <div class="mt-1">
                            @foreach($doc->tags_list as $tag)
                            <span class="badge bg-light text-dark border tag-badge me-1">{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                        @if($doc->date_expiration)
                        <div class="mt-1 small {{ $doc->est_expire ? 'text-danger' : ($doc->expire_bientot ? 'text-warning' : 'text-muted') }}">
                            <i class="bi bi-calendar-x me-1"></i>
                            Expire le {{ $doc->date_expiration->format('d/m/Y') }}
                            @if($doc->jours_avant_expiration !== null && !$doc->est_expire)
                                (dans {{ $doc->jours_avant_expiration }}j)
                            @elseif($doc->est_expire)
                                <span class="badge bg-danger ms-1">Expiré</span>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="col-auto d-flex flex-column gap-1 align-items-end">
                        <div class="text-muted small mb-1">{{ $doc->taille_lisible }}</div>
                        <div class="d-flex gap-1">
                            <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-outline-success" title="Télécharger">
                                <i class="bi bi-download"></i>
                            </a>
                            <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-secondary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('documents.modifier')
                            <a href="{{ route('documents.edit', $doc) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
            Aucun document trouvé.
            @can('documents.creer')
            <div class="mt-2">
                <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">Ajouter le premier document</a>
            </div>
            @endcan
        </div>
        @endforelse

        @if($documents->hasPages())
        <div class="mt-3">{{ $documents->links() }}</div>
        @endif
    </div>

</div>
@endsection
