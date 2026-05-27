@extends('layouts.app')

@section('title', $depense->reference . ' — Dépense')
@section('page-title', 'Détail de la dépense')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.depenses.index') }}">Dépenses</a></li>
    <li class="breadcrumb-item active">{{ $depense->reference }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

    <div class="d-flex gap-2 mb-4">
        @can('finance.depenses.approuver')
        @if($depense->statut === 'en_attente')
        <form action="{{ route('finance.depenses.approuver', $depense) }}" method="POST">
            @csrf
            <button class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Approuver</button>
        </form>
        <form action="{{ route('finance.depenses.rejeter', $depense) }}" method="POST"
              onsubmit="return confirm('Rejeter cette dépense ?')">
            @csrf
            <button class="btn btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Rejeter</button>
        </form>
        @endif
        @if(in_array($depense->statut, ['approuvee']))
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPayer">
            <i class="bi bi-cash me-1"></i>Marquer payée
        </button>
        @endif
        @endcan
        @can('finance.depenses.modifier')
        @if($depense->statut === 'en_attente')
        <a href="{{ route('finance.depenses.edit', $depense) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>Modifier
        </a>
        @endif
        @endcan
        <span class="badge bg-{{ $depense->statut_badge }} fs-6 align-self-center ms-auto">{{ $depense->statut_label }}</span>
    </div>

    <div class="card">
        <div class="card-header fw-semibold">
            <i class="bi bi-wallet2 me-2"></i>{{ $depense->reference }} — {{ $depense->libelle }}
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach([
                    ['Référence', $depense->reference],
                    ['Date', $depense->date_depense->format('d/m/Y')],
                    ['Catégorie', $depense->categorie?->nom ?? '—'],
                    ['Bénéficiaire', $depense->beneficiaire ?? '—'],
                    ['Fournisseur', $depense->fournisseur?->nom ?? '—'],
                    ['N° Pièce', $depense->numero_piece ?? '—'],
                    ['Mode paiement', ucfirst($depense->mode_paiement)],
                    ['Créé par', $depense->createdBy?->name ?? '—'],
                ] as [$label, $val])
                <div class="col-md-6">
                    <div class="d-flex">
                        <span class="text-muted small" style="width:150px;flex-shrink:0">{{ $label }}</span>
                        <span class="fw-semibold small">{{ $val }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            @if($depense->notes)
            <div class="mt-3 p-3 bg-light rounded">
                <strong>Notes :</strong> {{ $depense->notes }}
            </div>
            @endif
        </div>

        <div class="card-footer">
            <div class="row g-3 text-center">
                <div class="col-4">
                    <div class="text-muted small">Montant HT</div>
                    <div class="fw-bold">{{ number_format($depense->montant_ht, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="col-4">
                    <div class="text-muted small">TVA ({{ $depense->tva }} %)</div>
                    <div class="fw-bold">{{ number_format($depense->montant_ttc - $depense->montant_ht, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="col-4 bg-danger bg-opacity-10 rounded">
                    <div class="text-muted small">Montant TTC</div>
                    <div class="fw-bold fs-5 text-danger">{{ number_format($depense->montant_ttc, 0, ',', ' ') }} FCFA</div>
                </div>
            </div>
        </div>
    </div>

    @if($depense->approbateur)
    <div class="alert {{ $depense->statut === 'payee' ? 'alert-success' : 'alert-info' }} mt-3">
        <i class="bi bi-check-circle me-2"></i>
        {{ ucfirst($depense->statut) }} par <strong>{{ $depense->approbateur->name }}</strong>
        le {{ $depense->date_approbation?->format('d/m/Y à H:i') }}
    </div>
    @endif

</div>
</div>

{{-- Modal payer --}}
@can('finance.depenses.approuver')
<div class="modal fade" id="modalPayer" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Confirmer le paiement</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('finance.depenses.payer', $depense) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="bg-danger text-white rounded p-2 text-center fw-bold mb-3">
                        {{ number_format($depense->montant_ttc, 0, ',', ' ') }} FCFA
                    </div>
                    <label class="form-label">Mode de paiement</label>
                    <select name="mode_paiement" class="form-select" required>
                        <option value="especes">Espèces</option>
                        <option value="virement">Virement</option>
                        <option value="cheque">Chèque</option>
                        <option value="mobile_money">Mobile Money</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success w-100"><i class="bi bi-cash me-1"></i>Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
