@extends('layouts.app')

@section('title', $vente->numero_facture)
@section('page-title', 'Vente ' . $vente->numero_facture)

@section('content')
<div class="row g-4">

    {{-- Détails vente --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-{{ $vente->statut_badge }} me-2 fs-6">{{ $vente->statut_label }}</span>
                    <strong>{{ $vente->numero_facture }}</strong>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('ventes.facture', $vente) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="bi bi-printer me-1"></i>Imprimer
                    </a>
                    @if($vente->statut === 'confirmee')
                        <form method="POST" action="{{ route('ventes.livrer', $vente) }}">
                            @csrf
                            <button class="btn btn-sm btn-primary">
                                <i class="bi bi-truck me-1"></i>Marquer livrée
                            </button>
                        </form>
                    @endif
                    @if(! in_array($vente->statut, ['annulee', 'payee']))
                        <form method="POST" action="{{ route('ventes.annuler', $vente) }}"
                              onsubmit="return confirm('Annuler cette vente et restituer le stock ?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-x-circle me-1"></i>Annuler
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted small text-uppercase mb-2">Client</h6>
                        <div class="fw-semibold">{{ $vente->client->nom }}</div>
                        <div class="small text-muted">{{ $vente->client->type_label }}</div>
                        @if($vente->client->telephone)
                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $vente->client->telephone }}</div>
                        @endif
                        @if($vente->client->adresse)
                            <div class="small text-muted">{{ $vente->client->adresse }}</div>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <h6 class="text-muted small text-uppercase mb-2">Détails</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th class="text-muted small w-40">Date vente</th><td>{{ $vente->date_vente->format('d/m/Y') }}</td></tr>
                            <tr><th class="text-muted small">Paiement</th><td>{{ $vente->mode_paiement_label }}</td></tr>
                            @if($vente->date_livraison_prevue)
                                <tr><th class="text-muted small">Livraison prévue</th><td>{{ $vente->date_livraison_prevue->format('d/m/Y') }}</td></tr>
                            @endif
                            @if($vente->date_livraison_reelle)
                                <tr><th class="text-muted small">Livrée le</th><td class="text-success">{{ $vente->date_livraison_reelle->format('d/m/Y') }}</td></tr>
                            @endif
                            @if($vente->date_echeance)
                                <tr><th class="text-muted small">Échéance</th>
                                    <td class="{{ $vente->date_echeance->isPast() && !$vente->est_soldee ? 'text-danger fw-bold' : '' }}">
                                        {{ $vente->date_echeance->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @endif
                            <tr><th class="text-muted small">Vendeur</th><td>{{ $vente->user->name }}</td></tr>
                        </table>
                    </div>
                </div>

                {{-- Lignes --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Désignation</th>
                                <th>Réf.</th>
                                <th class="text-center">Qté</th>
                                <th class="text-end">P.U. HT</th>
                                <th class="text-center">Remise</th>
                                <th class="text-end">Total HT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vente->lignes as $i => $ligne)
                            <tr>
                                <td class="text-muted small">{{ $i + 1 }}</td>
                                <td>
                                    <a href="{{ route('equipements.show', $ligne->equipement) }}" class="text-decoration-none">
                                        {{ $ligne->designation_snapshot }}
                                    </a>
                                </td>
                                <td><code class="small">{{ $ligne->reference_snapshot }}</code></td>
                                <td class="text-center">{{ $ligne->quantite }}</td>
                                <td class="text-end">{{ number_format($ligne->prix_unitaire_ht, 0, ',', ' ') }}</td>
                                <td class="text-center small text-muted">{{ $ligne->remise > 0 ? $ligne->remise . '%' : '—' }}</td>
                                <td class="text-end fw-semibold">{{ number_format($ligne->total_ht, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="6" class="text-end text-muted small">Sous-total HT</td>
                                <td class="text-end">{{ number_format($vente->sous_total_ht, 0, ',', ' ') }}</td>
                            </tr>
                            @if($vente->montant_remise > 0)
                            <tr>
                                <td colspan="6" class="text-end text-muted small">Remise globale ({{ $vente->remise_globale }}%)</td>
                                <td class="text-end text-danger">- {{ number_format($vente->montant_remise, 0, ',', ' ') }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="6" class="text-end text-muted small">TVA ({{ $vente->tva }}%)</td>
                                <td class="text-end">{{ number_format($vente->montant_tva, 0, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end fw-bold">Total TTC</td>
                                <td class="text-end fw-bold fs-5 text-success">{{ number_format($vente->total_ttc, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($vente->notes)
                    <div class="mt-3 small text-muted"><strong>Notes :</strong> {{ $vente->notes }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Panneau paiement --}}
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-wallet2 me-2 text-success"></i>Paiement
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="fs-5 fw-bold">{{ number_format($vente->montant_paye, 0, ',', ' ') }}</div>
                        <div class="text-muted small">Payé (FCFA)</div>
                    </div>
                    <div class="col-6">
                        <div class="fs-5 fw-bold {{ $vente->reste_a_payer > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($vente->reste_a_payer, 0, ',', ' ') }}
                        </div>
                        <div class="text-muted small">Reste à payer</div>
                    </div>
                </div>

                <div class="progress mb-3" style="height:8px">
                    @php $pct = $vente->total_ttc > 0 ? min(100, ($vente->montant_paye / $vente->total_ttc) * 100) : 0; @endphp
                    <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                </div>

                @if(! $vente->est_soldee && $vente->statut !== 'annulee')
                <form method="POST" action="{{ route('ventes.paiement', $vente) }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small">Montant reçu (FCFA)</label>
                            <input type="number" name="montant" class="form-control form-control-sm"
                                min="0.01" max="{{ $vente->reste_a_payer }}"
                                value="{{ $vente->reste_a_payer }}" step="1">
                        </div>
                        <div class="col-12">
                            <select name="mode_paiement" class="form-select form-select-sm">
                                <option value="especes">Espèces</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="virement">Virement</option>
                                <option value="cheque">Chèque</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success w-100 btn-sm">
                                <i class="bi bi-check-circle me-1"></i>Enregistrer le paiement
                            </button>
                        </div>
                    </div>
                </form>
                @else
                    <div class="alert alert-success py-2 mb-0 text-center small">
                        <i class="bi bi-check-all me-1"></i>
                        @if($vente->statut === 'annulee') Vente annulée @else Entièrement soldée @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Lien vers les mouvements générés --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-arrow-left-right me-2 text-info"></i>Mouvements de stock
            </div>
            <div class="list-group list-group-flush">
                @foreach($vente->lignes as $ligne)
                <a href="{{ route('equipements.show', $ligne->equipement) }}"
                   class="list-group-item list-group-item-action small d-flex justify-content-between">
                    <span>{{ Str::limit($ligne->designation_snapshot, 30) }}</span>
                    <span class="badge bg-danger">-{{ $ligne->quantite }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
