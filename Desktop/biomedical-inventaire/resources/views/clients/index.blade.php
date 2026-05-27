@extends('layouts.app')

@section('title', 'Clients')
@section('page-title', 'Clients')

@section('content')

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="recherche" class="form-control form-control-sm"
                    placeholder="Nom, code, email, téléphone..." value="{{ request('recherche') }}">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous types</option>
                    @foreach(['hopital' => 'Hôpital', 'clinique' => 'Clinique', 'cabinet' => 'Cabinet', 'laboratoire' => 'Laboratoire', 'particulier' => 'Particulier', 'autre' => 'Autre'] as $v => $l)
                        <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('clients.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Nouveau client
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Ville</th>
                    <th>Contact</th>
                    <th class="text-center">Ventes</th>
                    <th class="text-center">Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $c)
                <tr>
                    <td><code class="small">{{ $c->code_client }}</code></td>
                    <td>
                        <a href="{{ route('clients.show', $c) }}" class="fw-medium text-decoration-none">{{ $c->nom }}</a>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $c->type_label }}</span>
                    </td>
                    <td class="small text-muted">{{ $c->ville ?? '—' }}</td>
                    <td class="small">
                        @if($c->telephone)<div><i class="bi bi-telephone me-1 text-muted"></i>{{ $c->telephone }}</div>@endif
                        @if($c->email)<div><i class="bi bi-envelope me-1 text-muted"></i>{{ $c->email }}</div>@endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('ventes.index', ['client_id' => $c->id]) }}" class="badge bg-primary text-decoration-none">
                            {{ $c->ventes_count }}
                        </a>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $c->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($c->statut) }}</span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('clients.show', $c) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('clients.edit', $c) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <a href="{{ route('ventes.create', ['client_id' => $c->id]) }}" class="btn btn-outline-success" title="Nouvelle vente">
                                <i class="bi bi-cart-plus"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-people fs-1 d-block mb-2"></i>Aucun client enregistré
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $clients->links() }}</div>
@endsection
