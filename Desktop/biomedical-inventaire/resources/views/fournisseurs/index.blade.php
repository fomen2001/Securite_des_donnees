@extends('layouts.app')

@section('title', 'Fournisseurs')
@section('page-title', 'Fournisseurs')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">{{ $fournisseurs->total() }} fournisseur(s)</span>
    <a href="{{ route('fournisseurs.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Ajouter un fournisseur
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Contact</th>
                    <th>Email / Tél</th>
                    <th>Pays</th>
                    <th class="text-center">Équipements</th>
                    <th class="text-center">Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fournisseurs as $f)
                <tr>
                    <td>
                        <a href="{{ route('fournisseurs.show', $f) }}" class="fw-medium text-decoration-none">
                            {{ $f->nom }}
                        </a>
                        @if($f->site_web)
                            <a href="{{ $f->site_web }}" target="_blank" class="ms-1 text-muted small">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $f->contact_nom ?? '—' }}</td>
                    <td class="small">
                        @if($f->email)
                            <div><i class="bi bi-envelope me-1 text-muted"></i>{{ $f->email }}</div>
                        @endif
                        @if($f->telephone)
                            <div><i class="bi bi-telephone me-1 text-muted"></i>{{ $f->telephone }}</div>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $f->pays ?? '—' }}</td>
                    <td class="text-center">
                        <a href="{{ route('equipements.index', ['fournisseur_id' => $f->id]) }}"
                           class="badge bg-primary text-decoration-none">
                            {{ $f->equipements_count }}
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $f->statut === 'actif' ? 'success' : 'secondary' }}">
                            {{ ucfirst($f->statut) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('fournisseurs.show', $f) }}" class="btn btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('fournisseurs.edit', $f) }}" class="btn btn-outline-secondary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('fournisseurs.destroy', $f) }}"
                                  onsubmit="return confirm('Archiver ce fournisseur ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Archiver">
                                    <i class="bi bi-archive"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Aucun fournisseur enregistré
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $fournisseurs->links() }}</div>

@endsection
