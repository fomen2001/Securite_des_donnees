@extends('layouts.app')

@section('title', 'Bilans comptables')
@section('page-title', 'Bilans comptables (SYSCOHADA)')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('impots.dashboard') }}">Fiscalité</a></li>
    <li class="breadcrumb-item active">Bilan comptable</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Bilans annuels — Norme SYSCOHADA (OHADA)</p>
    <a href="{{ route('impots.bilan.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau bilan</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Exercice</th>
                    <th class="text-end">Total Actif</th>
                    <th class="text-end">CA</th>
                    <th class="text-end">Résultat net</th>
                    <th class="text-center">Équilibre</th>
                    <th class="text-center">Statut</th>
                    <th>Dépôt DSF</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bilans as $b)
                <tr>
                    <td class="fw-bold fs-5">{{ $b->exercice }}</td>
                    <td class="text-end">{{ number_format($b->total_actif, 0, ',', ' ') }} FCFA</td>
                    <td class="text-end">{{ number_format($b->chiffre_affaires, 0, ',', ' ') }} FCFA</td>
                    <td class="text-end fw-semibold {{ $b->resultat_net >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $b->resultat_net >= 0 ? '+' : '' }}{{ number_format($b->resultat_net, 0, ',', ' ') }} FCFA
                    </td>
                    <td class="text-center">
                        @if($b->equilibre)
                            <i class="bi bi-check-circle-fill text-success" title="Bilan équilibré"></i>
                        @else
                            <i class="bi bi-exclamation-triangle-fill text-danger" title="Déséquilibre actif/passif"></i>
                        @endif
                    </td>
                    <td class="text-center"><span class="badge bg-{{ $b->statut_badge }}">{{ $b->statut_label }}</span></td>
                    <td>{{ $b->date_depot ? $b->date_depot->format('d/m/Y') : '—' }}</td>
                    <td>
                        <a href="{{ route('impots.bilan.show', $b->exercice) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-journal-text fs-3 d-block mb-2"></i>Aucun bilan comptable enregistré.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bilans->hasPages())
    <div class="card-footer">{{ $bilans->links() }}</div>
    @endif
</div>
@endsection
