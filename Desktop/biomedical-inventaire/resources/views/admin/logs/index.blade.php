@extends('layouts.app')

@section('title', 'Journal d\'activité')
@section('page-title', 'Journal d\'activité')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Journal</li>
@endsection

@push('styles')
<style>
.log-row:hover { background: #f8faff; }
.subject-badge { font-size: .7rem; padding: 2px 7px; border-radius: 20px; }
</style>
@endpush

@section('content')

{{-- Filtres --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Utilisateur</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Tous les utilisateurs</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Module</label>
                <select name="log_name" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($logNames as $ln)
                    <option value="{{ $ln }}" {{ request('log_name') == $ln ? 'selected' : '' }}>
                        {{ $ln }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Du</label>
                <input type="date" name="date_debut" class="form-control form-control-sm"
                       value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Au</label>
                <input type="date" name="date_fin" class="form-control form-control-sm"
                       value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Recherche</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Mot-clé..." value="{{ request('search') }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-clock-history me-1"></i>Historique des actions</span>
        <span class="badge bg-secondary">{{ $logs->total() }} entrée(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:140px">Date / Heure</th>
                    <th style="width:160px">Utilisateur</th>
                    <th>Description</th>
                    <th style="width:130px">Objet</th>
                    <th style="width:90px">Module</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                @php
                    $subjectClass = $log->subject_type ? class_basename($log->subject_type) : null;
                    $subjectColor = match($subjectClass) {
                        'Equipement'    => 'primary',
                        'Vente'         => 'success',
                        'Maintenance'   => 'warning',
                        'MouvementStock'=> 'info',
                        'Client'        => 'secondary',
                        'User'          => 'danger',
                        default         => 'light',
                    };
                @endphp
                <tr class="log-row">
                    <td class="text-muted small" style="white-space:nowrap">
                        {{ $log->created_at->format('d/m/Y') }}<br>
                        <span style="font-size:.75rem">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        @if($log->causer)
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                 style="width:28px;height:28px;font-size:.75rem;font-weight:600;flex-shrink:0">
                                {{ strtoupper(substr($log->causer->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="small fw-semibold lh-1">{{ $log->causer->name }}</div>
                                <div style="font-size:.7rem" class="text-muted">
                                    {{ $log->causer->roles->first()?->name ?? '—' }}
                                </div>
                            </div>
                        </div>
                        @else
                            <span class="text-muted small">Système</span>
                        @endif
                    </td>
                    <td>
                        <div class="small">{{ $log->description }}</div>
                        @if($log->properties && $log->properties->get('attributes'))
                        <button class="btn btn-link btn-sm p-0 text-muted" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#props-{{ $log->id }}">
                            <small>Voir détails</small>
                        </button>
                        <div class="collapse mt-1" id="props-{{ $log->id }}">
                            <pre class="bg-light rounded p-2" style="font-size:.7rem;max-height:150px;overflow:auto">{{ json_encode($log->properties->get('attributes'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($subjectClass)
                        <span class="subject-badge bg-{{ $subjectColor }} bg-opacity-15 text-{{ $subjectColor }} border border-{{ $subjectColor }}">
                            {{ $subjectClass }}
                            @if($log->subject_id) #{{ $log->subject_id }} @endif
                        </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $log->log_name ?? 'default' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                        Aucune activité enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $logs->links() }}</div>

@endsection
