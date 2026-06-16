@extends('layouts.app')
@section('title', $message->reference)
@section('page-title', 'Message — ' . $message->reference)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('secretariat.messages.index') }}">Messages</a></li>
    <li class="breadcrumb-item active">{{ $message->reference }}</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- ── Colonne principale ──────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Entête message --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-envelope-paper me-2"></i>{{ $message->objet }}</span>
                <span class="badge bg-{{ $message->statut_badge }} fs-6">{{ $message->statut_label }}</span>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3 text-center">
                    <div class="col-4">
                        <div class="small text-muted">Référence</div>
                        <div class="font-monospace fw-semibold">{{ $message->reference }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Canal</div>
                        <div>{{ $message->canal_label }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Envoyé le</div>
                        <div>{{ $message->envoye_le ? $message->envoye_le->format('d/m/Y H:i') : '—' }}</div>
                    </div>
                </div>

                {{-- Corps --}}
                <div class="border rounded p-3 bg-light" style="white-space:pre-wrap;font-size:.95rem;line-height:1.8">{{ $message->corps }}</div>
            </div>
        </div>

        {{-- Pièces jointes --}}
        @if($message->piecesJointes->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-paperclip me-2 text-success"></i>
                Pièces jointes ({{ $message->piecesJointes->count() }})
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($message->piecesJointes as $pj)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 border rounded p-2 bg-light">
                            <i class="bi {{ $pj->icone }} fs-4 flex-shrink-0"></i>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold small text-truncate" title="{{ $pj->nom_original }}">
                                    {{ $pj->nom_original }}
                                </div>
                                <div class="text-muted" style="font-size:.72rem">{{ $pj->taille_lisible }}</div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <a href="{{ route('secretariat.messages.pieces.telecharger', $pj) }}"
                                   class="btn btn-xs btn-outline-primary" title="Télécharger">
                                    <i class="bi bi-download"></i>
                                </a>
                                @can('secretariat.messages.envoyer')
                                @if($message->statut === 'brouillon')
                                <form action="{{ route('secretariat.messages.pieces.supprimer', $pj) }}"
                                      method="POST" onsubmit="return confirm('Supprimer cette pièce jointe ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Destinataires --}}
        <div class="card">
            <div class="card-header fw-semibold">
                <i class="bi bi-people me-2"></i>Destinataires ({{ $message->destinataires->count() }})
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">SMS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($message->destinataires as $dest)
                        <tr>
                            <td class="fw-semibold">{{ $dest->client->nom }}</td>
                            <td class="small text-muted">{{ $dest->email_copie ?? '—' }}</td>
                            <td class="small text-muted">{{ $dest->telephone_copie ?? '—' }}</td>
                            <td class="text-center">
                                @php $b = match($dest->statut_email) { 'envoye'=>'success','echoue'=>'danger','en_attente'=>'warning',default=>'secondary' }; @endphp
                                <span class="badge bg-{{ $b }}">{{ $dest->statut_email }}</span>
                            </td>
                            <td class="text-center">
                                @php $b = match($dest->statut_sms) { 'envoye'=>'success','echoue'=>'danger','en_attente'=>'warning',default=>'secondary' }; @endphp
                                <span class="badge bg-{{ $b }}">{{ $dest->statut_sms }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Colonne latérale ────────────────────────────────────── --}}
    <div class="col-lg-4">

        @can('secretariat.messages.envoyer')
        @if($message->statut === 'brouillon')
        <div class="card mb-3">
            <div class="card-body d-grid">
                <form action="{{ route('secretariat.messages.envoyer', $message) }}" method="POST">
                    @csrf
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-send me-1"></i>Envoyer ce message
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endcan

        {{-- Statistiques envoi --}}
        @if($message->statut !== 'brouillon')
        <div class="card mb-3">
            <div class="card-header fw-semibold">Résultat de l'envoi</div>
            <div class="card-body">
                @php
                    $envoyes = $message->destinataires->where('statut_email', 'envoye')->count();
                    $echecs  = $message->destinataires->where('statut_email', 'echoue')->count();
                    $total   = $message->destinataires->count();
                @endphp
                <div class="d-flex justify-content-between mb-1 small">
                    <span class="text-success"><i class="bi bi-check-circle me-1"></i>Envoyés</span>
                    <strong class="text-success">{{ $envoyes }}/{{ $total }}</strong>
                </div>
                <div class="progress mb-2" style="height:6px">
                    <div class="progress-bar bg-success" style="width:{{ $total > 0 ? round($envoyes/$total*100) : 0 }}%"></div>
                </div>
                @if($echecs > 0)
                <div class="d-flex justify-content-between small">
                    <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Échecs</span>
                    <strong class="text-danger">{{ $echecs }}</strong>
                </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header fw-semibold">Informations</div>
            <div class="card-body small text-muted">
                Rédigé par <strong>{{ $message->user->name }}</strong><br>
                le {{ $message->created_at->format('d/m/Y à H:i') }}
                @if($message->piecesJointes->isNotEmpty())
                <hr class="my-2">
                <i class="bi bi-paperclip me-1"></i>
                {{ $message->piecesJointes->count() }} pièce(s) jointe(s)<br>
                Taille totale :
                <strong>{{ round($message->piecesJointes->sum('taille') / 1024) }} Ko</strong>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>.btn-xs { padding:.2rem .5rem; font-size:.75rem; }</style>
@endpush
