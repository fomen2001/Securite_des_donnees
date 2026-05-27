@extends('layouts.app')

@section('title', 'Nouvel utilisateur')
@section('page-title', 'Créer un utilisateur')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.utilisateurs.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-6">

<div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-person-plus me-1"></i>Nouveau compte utilisateur
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.utilisateurs.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Adresse email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">— Choisir un rôle —</option>
                    @foreach($roles as $role)
                    @php
                        $label = match($role->name) {
                            'admin'                  => 'Administrateur',
                            'responsable_vente'      => 'Responsable des ventes',
                            'technicien_maintenance' => 'Technicien de maintenance',
                            'gestionnaire_stock'     => 'Gestionnaire de stock',
                            'lecteur'                => 'Lecteur (consultation)',
                            default                  => $role->name,
                        };
                    @endphp
                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Confirmer le mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Créer l'utilisateur
                </button>
                <a href="{{ route('admin.utilisateurs.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
@endsection
