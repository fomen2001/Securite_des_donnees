@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')
@section('page-title', 'Utilisateurs & Rôles')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Utilisateurs</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-secondary fs-6">{{ $users->total() }} utilisateur(s)</span>
    </div>
    <a href="{{ route('admin.utilisateurs.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Nouvel utilisateur
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Inscrit le</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="{{ $user->id === auth()->id() ? 'table-primary' : '' }}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                 style="width:34px;height:34px;font-size:.85rem;font-weight:600;flex-shrink:0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                    <small class="text-muted">Vous</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-muted">{{ $user->email }}</td>
                    <td>
                        @foreach($user->roles as $role)
                            @php
                                $color = match($role->name) {
                                    'admin'                  => 'danger',
                                    'responsable_vente'      => 'success',
                                    'technicien_maintenance' => 'warning',
                                    'gestionnaire_stock'     => 'info',
                                    default                  => 'secondary',
                                };
                                $label = match($role->name) {
                                    'admin'                  => 'Administrateur',
                                    'responsable_vente'      => 'Resp. Ventes',
                                    'technicien_maintenance' => 'Technicien',
                                    'gestionnaire_stock'     => 'Gestionnaire Stock',
                                    'lecteur'                => 'Lecteur',
                                    default                  => $role->name,
                                };
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ $label }}</span>
                        @endforeach
                        @if($user->roles->isEmpty())
                            <span class="text-muted small">Aucun rôle</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.utilisateurs.edit', $user) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.utilisateurs.destroy', $user) }}"
                              class="d-inline"
                              onsubmit="return confirm('Supprimer {{ $user->name }} ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Aucun utilisateur.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $users->links() }}</div>

{{-- Résumé des rôles disponibles --}}
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-shield-lock me-1"></i>Rôles disponibles
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($roles as $role)
            @php
                $color = match($role->name) {
                    'admin'                  => 'danger',
                    'responsable_vente'      => 'success',
                    'technicien_maintenance' => 'warning',
                    'gestionnaire_stock'     => 'info',
                    default                  => 'secondary',
                };
                $desc = match($role->name) {
                    'admin'                  => 'Accès total à toutes les fonctionnalités.',
                    'responsable_vente'      => 'Ventes, clients, facturation et paiements.',
                    'technicien_maintenance' => 'Maintenances, équipements (lecture/modif), mouvements.',
                    'gestionnaire_stock'     => 'Stock, équipements complets, fournisseurs, référentiels.',
                    'lecteur'                => 'Consultation uniquement, aucune modification.',
                    default                  => '',
                };
            @endphp
            <div class="col-md-4">
                <div class="p-3 rounded border border-{{ $color }} bg-{{ $color }} bg-opacity-10">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-{{ $color }}">{{ $role->name }}</span>
                        <small class="text-muted">{{ $role->permissions->count() }} permissions</small>
                    </div>
                    <p class="mb-0 small text-muted">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
