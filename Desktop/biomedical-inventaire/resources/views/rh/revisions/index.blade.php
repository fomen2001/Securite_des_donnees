@extends('layouts.app')

@section('title', 'Révisions salariales')
@section('page-title', 'Historique des révisions salariales')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item active">Révisions salariales</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <span class="text-muted">{{ $revisions->total() }} révision(s)</span>
    @can('rh.employes.modifier')
    <a href="{{ route('rh.revisions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nouvelle révision
    </a>
    @endcan
</div>

<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <select name="employe_id" class="form-select form-select-sm">
                    <option value="">Tous les employés</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}" @selected(request('employe_id') == $e->id)>{{ $e->nom_complet }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="annee" class="form-select form-select-sm">
                    <option value="">Toutes années</option>
                    @foreach($annees as $a)
                        <option value="{{ $a }}" @selected(request('annee') == $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-1">
                <button class="btn btn-sm btn-secondary flex-fill"><i class="bi bi-search"></i> Filtrer</button>
                <a href="{{ route('rh.revisions.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
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
                    <th>Date d'effet</th>
                    <th class="text-end">Ancien salaire</th>
                    <th class="text-end">Nouveau salaire</th>
                    <th class="text-center">Variation</th>
                    <th>Motif</th>
                    <th>Approuvé par</th>
                </tr>
            </thead>
            <tbody>
                @forelse($revisions as $r)
                <tr>
                    <td>
                        <a href="{{ route('rh.employes.show', $r->employe) }}" class="fw-semibold text-decoration-none">
                            {{ $r->employe->nom_complet }}
                        </a>
                        <small class="text-muted d-block">{{ $r->employe->poste }}</small>
                    </td>
                    <td>{{ $r->date_effet->format('d/m/Y') }}</td>
                    <td class="text-end">{{ number_format($r->ancien_salaire, 0, ',', ' ') }} FCFA</td>
                    <td class="text-end fw-semibold">{{ number_format($r->nouveau_salaire, 0, ',', ' ') }} FCFA</td>
                    <td class="text-center">
                        @php $v = $r->variation; @endphp
                        <span class="badge {{ $v >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $r->variation_formattee }}
                        </span>
                    </td>
                    <td><small>{{ $r->motif_label }}</small></td>
                    <td><small class="text-muted">{{ $r->approbateur?->name ?? '—' }}</small></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-graph-up-arrow fs-3 d-block mb-2"></i>Aucune révision enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($revisions->hasPages())
    <div class="card-footer">{{ $revisions->links() }}</div>
    @endif
</div>
@endsection
