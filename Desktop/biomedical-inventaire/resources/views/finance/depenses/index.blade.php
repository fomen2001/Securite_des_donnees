@extends('layouts.app')

@section('title', 'Dépenses')
@section('page-title', 'Gestion des dépenses')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item active">Dépenses</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <span class="text-muted">{{ $depenses->total() }} dépense(s)</span>
    @can('finance.depenses.creer')
    <a href="{{ route('finance.depenses.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Nouvelle dépense
    </a>
    @endcan
</div>

<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="recherche" value="{{ request('recherche') }}" class="form-control form-control-sm" placeholder="Libellé, référence, bénéficiaire...">
            </div>
            <div class="col-md-2">
                <select name="categorie_id" class="form-select form-select-sm">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('categorie_id') == $c->id)>{{ $c->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="en_attente" @selected(request('statut')=='en_attente')>En attente</option>
                    <option value="approuvee" @selected(request('statut')=='approuvee')>Approuvée</option>
                    <option value="payee" @selected(request('statut')=='payee')>Payée</option>
                    <option value="rejetee" @selected(request('statut')=='rejetee')>Rejetée</option>
                </select>
            </div>
            <div class="col-md-1">
                <select name="mois" class="form-select form-select-sm">
                    <option value="">Mois</option>
                    @foreach(['1'=>'Jan','2'=>'Fév','3'=>'Mar','4'=>'Avr','5'=>'Mai','6'=>'Juin','7'=>'Juil','8'=>'Aoû','9'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Déc'] as $m=>$l)
                        <option value="{{ $m }}" @selected(request('mois') == $m)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <select name="annee" class="form-select form-select-sm">
                    <option value="">Année</option>
                    @foreach($annees as $a)
                        <option value="{{ $a }}" @selected(request('annee') == $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-sm btn-secondary flex-fill"><i class="bi bi-search"></i> Filtrer</button>
                <a href="{{ route('finance.depenses.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Libellé</th>
                    <th>Catégorie</th>
                    <th class="text-end">Montant HT</th>
                    <th class="text-end">TTC</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($depenses as $d)
                <tr>
                    <td><code class="small">{{ $d->reference }}</code></td>
                    <td>{{ $d->date_depense->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('finance.depenses.show', $d) }}" class="text-decoration-none fw-semibold">
                            {{ Str::limit($d->libelle, 40) }}
                        </a>
                        @if($d->beneficiaire)
                        <small class="text-muted d-block">{{ $d->beneficiaire }}</small>
                        @endif
                    </td>
                    <td>
                        @if($d->categorie)
                        <span class="badge" style="background:{{ $d->categorie->couleur }}">{{ $d->categorie->nom }}</span>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($d->montant_ht, 0, ',', ' ') }}</td>
                    <td class="text-end fw-semibold">{{ number_format($d->montant_ttc, 0, ',', ' ') }}</td>
                    <td><span class="badge bg-{{ $d->statut_badge }}">{{ $d->statut_label }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('finance.depenses.show', $d) }}" class="btn btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            @can('finance.depenses.approuver')
                            @if($d->statut === 'en_attente')
                            <form action="{{ route('finance.depenses.approuver', $d) }}" method="POST">
                                @csrf
                                <button class="btn btn-outline-success" title="Approuver"><i class="bi bi-check-lg"></i></button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-wallet2 fs-3 d-block mb-2"></i>Aucune dépense trouvée.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($depenses->count())
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="6" class="text-end">Total (filtre courant) :</td>
                    <td class="text-end text-danger">{{ number_format($totalFiltre, 0, ',', ' ') }} FCFA</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if($depenses->hasPages())
    <div class="card-footer">{{ $depenses->links() }}</div>
    @endif
</div>
@endsection
