@extends('layouts.app')
@section('title', 'Messages clients')
@section('page-title', 'Messagerie clients')
@section('breadcrumb')
    <li class="breadcrumb-item active">Secrétariat</li>
    <li class="breadcrumb-item active">Messages</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="alert alert-info py-2 mb-0 small flex-grow-1 me-3">
        <i class="bi bi-info-circle me-1"></i>
        L'envoi <strong>email</strong> est actif. L'envoi <strong>SMS</strong> nécessite la configuration d'une API (Africa's Talking).
    </div>
    @can('secretariat.messages.envoyer')
    <a href="{{ route('secretariat.messages.create') }}" class="btn btn-primary">
        <i class="bi bi-envelope-plus me-1"></i>Nouveau message
    </a>
    @endcan
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form class="row g-2 align-items-center" method="GET">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       placeholder="Objet, référence…">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="brouillon"            {{ request('statut')=='brouillon'?'selected':'' }}>Brouillon</option>
                    <option value="envoye"               {{ request('statut')=='envoye'?'selected':'' }}>Envoyé</option>
                    <option value="partiellement_envoye" {{ request('statut')=='partiellement_envoye'?'selected':'' }}>Partiel</option>
                    <option value="echoue"               {{ request('statut')=='echoue'?'selected':'' }}>Échoué</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="canal" class="form-select form-select-sm">
                    <option value="">Tous canaux</option>
                    <option value="email"     {{ request('canal')=='email'?'selected':'' }}>Email</option>
                    <option value="sms"       {{ request('canal')=='sms'?'selected':'' }}>SMS</option>
                    <option value="email_sms" {{ request('canal')=='email_sms'?'selected':'' }}>Email + SMS</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="{{ route('secretariat.messages.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Réinitialiser</a>
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
                    <th>Objet</th>
                    <th>Canal</th>
                    <th class="text-center">Destinataires</th>
                    <th>Envoyé le</th>
                    <th>Rédigé par</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                <tr>
                    <td><span class="font-monospace small">{{ $msg->reference }}</span></td>
                    <td class="fw-semibold">{{ Str::limit($msg->objet, 45) }}</td>
                    <td>
                        @if($msg->canal === 'email')
                            <span class="badge bg-primary">Email</span>
                        @elseif($msg->canal === 'sms')
                            <span class="badge bg-success">SMS</span>
                        @else
                            <span class="badge bg-info">Email + SMS</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ $msg->destinataires_count }}</span>
                    </td>
                    <td class="small text-muted">
                        {{ $msg->envoye_le ? $msg->envoye_le->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td class="small">{{ $msg->user->name }}</td>
                    <td><span class="badge bg-{{ $msg->statut_badge }}">{{ $msg->statut_label }}</span></td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('secretariat.messages.show', $msg) }}"
                               class="btn btn-xs btn-outline-primary"><i class="bi bi-eye"></i></a>
                            @if($msg->statut === 'brouillon')
                            @can('secretariat.messages.envoyer')
                            <form action="{{ route('secretariat.messages.envoyer', $msg) }}" method="POST">
                                @csrf
                                <button class="btn btn-xs btn-success" title="Envoyer maintenant">
                                    <i class="bi bi-send"></i>
                                </button>
                            </form>
                            @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-envelope fs-2 d-block mb-2"></i>Aucun message.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($messages->hasPages())
    <div class="card-footer">{{ $messages->links() }}</div>
    @endif
</div>
@endsection
@push('styles')
<style>.btn-xs{padding:.2rem .5rem;font-size:.75rem;}</style>
@endpush
