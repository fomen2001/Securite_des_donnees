@extends('layouts.app')
@section('title', 'Rapports de réunion')
@section('page-title', 'Rapports de réunion')
@section('breadcrumb')
    <li class="breadcrumb-item active">Secrétariat</li>
    <li class="breadcrumb-item active">Réunions</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    @can('secretariat.reunions.gerer')
    <a href="{{ route('secretariat.reunions.create') }}" class="btn btn-primary">
        <i class="bi bi-journal-plus me-1"></i>Nouveau rapport
    </a>
    @endcan
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form class="row g-2 align-items-center" method="GET">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       placeholder="Titre, référence, lieu…">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous types</option>
                    <option value="interne"     {{ request('type')=='interne'?'selected':'' }}>Interne</option>
                    <option value="client"      {{ request('type')=='client'?'selected':'' }}>Avec client</option>
                    <option value="fournisseur" {{ request('type')=='fournisseur'?'selected':'' }}>Avec fournisseur</option>
                    <option value="partenaire"  {{ request('type')=='partenaire'?'selected':'' }}>Avec partenaire</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="brouillon" {{ request('statut')=='brouillon'?'selected':'' }}>Brouillon</option>
                    <option value="finalise"  {{ request('statut')=='finalise'?'selected':'' }}>Finalisé</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="{{ route('secretariat.reunions.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Lieu</th>
                    <th class="text-center">Participants</th>
                    <th>Rédacteur</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rapports as $r)
                <tr>
                    <td><span class="font-monospace small">{{ $r->reference }}</span></td>
                    <td class="fw-semibold">{{ Str::limit($r->titre, 40) }}</td>
                    <td><span class="badge bg-{{ $r->type_badge }}">{{ $r->type_label }}</span></td>
                    <td class="small">{{ $r->date_reunion->format('d/m/Y') }}</td>
                    <td class="small text-muted">{{ $r->lieu ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ $r->participants_count }}</span>
                    </td>
                    <td class="small">{{ $r->user->name }}</td>
                    <td>
                        @if($r->statut === 'finalise')
                            <span class="badge bg-success">Finalisé</span>
                        @else
                            <span class="badge bg-secondary">Brouillon</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('secretariat.reunions.show', $r) }}"
                               class="btn btn-xs btn-outline-primary"><i class="bi bi-eye"></i></a>
                            @can('secretariat.reunions.gerer')
                            <a href="{{ route('secretariat.reunions.edit', $r) }}"
                               class="btn btn-xs btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            <a href="{{ route('secretariat.reunions.imprimer', $r) }}" target="_blank"
                               class="btn btn-xs btn-outline-dark"><i class="bi bi-printer"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-journal fs-2 d-block mb-2"></i>Aucun rapport de réunion.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rapports->hasPages())
    <div class="card-footer">{{ $rapports->links() }}</div>
    @endif
</div>
@endsection
@push('styles')
<style>.btn-xs{padding:.2rem .5rem;font-size:.75rem;}</style>
@endpush
