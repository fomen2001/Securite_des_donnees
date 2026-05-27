@extends('layouts.app')

@section('title', 'Employés — Module RH')
@section('page-title', 'Employés')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item active">Employés</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="text-muted">{{ $employes->total() }} employé(s) trouvé(s)</span>
    </div>
    @can('rh.employes.creer')
    <a href="{{ route('rh.employes.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Nouvel employé
    </a>
    @endcan
</div>

{{-- Filtres --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="recherche" value="{{ request('recherche') }}"
                       class="form-control form-control-sm" placeholder="Nom, matricule, poste...">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    @foreach(['actif'=>'Actif','conge'=>'En congé','suspendu'=>'Suspendu','demissionne'=>'Démissionné','licencie'=>'Licencié'] as $v => $l)
                        <option value="{{ $v }}" @selected(request('statut') == $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type_contrat" class="form-select form-select-sm">
                    <option value="">Tous contrats</option>
                    @foreach(['CDI','CDD','stage','consultant'] as $c)
                        <option value="{{ $c }}" @selected(request('type_contrat') == $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="service_id" class="form-select form-select-sm">
                    <option value="">Tous services</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" @selected(request('service_id') == $s->id)>{{ $s->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-sm btn-secondary flex-fill">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <a href="{{ route('rh.employes.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Matricule</th>
                    <th>Nom & Prénom</th>
                    <th>Poste</th>
                    <th>Service</th>
                    <th>Contrat</th>
                    <th class="text-end">Salaire de base</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($employes as $e)
                <tr>
                    <td><code>{{ $e->matricule }}</code></td>
                    <td>
                        <div class="fw-semibold">{{ $e->nom }} {{ $e->prenom }}</div>
                        <small class="text-muted">{{ $e->email }}</small>
                    </td>
                    <td>{{ $e->poste }}</td>
                    <td>{{ $e->service?->nom ?? '—' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $e->type_contrat }}</span></td>
                    <td class="text-end fw-semibold">{{ number_format($e->salaire_base, 0, ',', ' ') }} FCFA</td>
                    <td>
                        @php $colors = ['actif'=>'success','conge'=>'info','suspendu'=>'warning','demissionne'=>'secondary','licencie'=>'danger']; @endphp
                        <span class="badge bg-{{ $colors[$e->statut] ?? 'secondary' }}">{{ $e->statut_label }}</span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('rh.employes.show', $e) }}" class="btn btn-outline-primary" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('rh.employes.modifier')
                            <a href="{{ route('rh.employes.edit', $e) }}" class="btn btn-outline-secondary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            <a href="{{ route('rh.bulletins.create', ['employe_id' => $e->id]) }}" class="btn btn-outline-success" title="Générer bulletin">
                                <i class="bi bi-file-earmark-text"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-people fs-3 d-block mb-2"></i>Aucun employé trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employes->hasPages())
    <div class="card-footer">{{ $employes->links() }}</div>
    @endif
</div>
@endsection
